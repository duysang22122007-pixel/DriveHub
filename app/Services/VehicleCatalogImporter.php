<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;
use Throwable;

final class VehicleCatalogImporter
{
    public function __construct(private readonly PDO $pdo, private readonly VehicleDataNormalizer $normalizer)
    {
    }

    /**
     * Imports one make and its models atomically with the session cursor.
     *
     * @param array{source_make_id: int, name: string} $make
     * @param list<array<string, mixed>> $modelRows
     * @return array{added: int, duplicates: int, errors: int}
     */
    public function importMake(
        int $sessionId,
        array $make,
        array $modelRows,
        string $mode,
        int $cursor,
        int $total
    ): array {
        $this->pdo->beginTransaction();

        try {
            $lockSession = $this->pdo->prepare(
                'SELECT next_cursor FROM vehicle_import_sessions WHERE id = :id FOR UPDATE'
            );
            $lockSession->execute(['id' => $sessionId]);
            $currentCursor = $lockSession->fetchColumn();
            if ($currentCursor === false) {
                throw new RuntimeException('Import session no longer exists.');
            }
            if ((int) $currentCursor > $cursor) {
                $this->pdo->rollBack();

                return ['added' => 0, 'duplicates' => 0, 'errors' => 0];
            }
            if ((int) $currentCursor !== $cursor) {
                throw new RuntimeException('Import session progress changed; reload before continuing.');
            }

            $added = 0;
            $duplicates = 0;
            $errors = [];
            $brandId = $this->findBrandBySourceId($make['source_make_id']);

            if ($mode !== 'models' && $brandId !== null) {
                $duplicates++;
            }

            if ($mode !== 'models' && $brandId === null) {
                $existingBrand = $this->findBrandByName($make['name']);
                if ($existingBrand !== null) {
                    $duplicates++;
                    $brandId = (int) $existingBrand['id'];
                    if ($existingBrand['source_make_id'] === null) {
                        $this->pdo->prepare(
                            "UPDATE vehicle_brands SET source_make_id = :source_make_id
                             WHERE id = :id AND data_source = 'admin' AND source_make_id IS NULL"
                        )->execute([
                            'source_make_id' => $make['source_make_id'],
                            'id' => $brandId,
                        ]);
                    } elseif ((int) $existingBrand['source_make_id'] !== $make['source_make_id']) {
                        $errors[] = [
                            'stage' => 'make',
                            'source_make_id' => $make['source_make_id'],
                            'source_model_id' => null,
                            'message' => 'A local brand with the same name is mapped to a different NHTSA make.',
                        ];
                        $brandId = null;
                    }
                } else {
                    $insertBrand = $this->pdo->prepare(
                        "INSERT INTO vehicle_brands
                            (name, status, data_source, source_make_id, verification_status)
                         VALUES (:name, 1, 'nhtsa', :source_make_id, 'unverified')"
                    );
                    $insertBrand->execute([
                        'name' => $make['name'],
                        'source_make_id' => $make['source_make_id'],
                    ]);
                    $brandId = (int) $this->pdo->lastInsertId();
                    $added++;
                }
            }

            if ($brandId === null && $mode !== 'brands') {
                $errors[] = [
                    'stage' => 'model',
                    'source_make_id' => $make['source_make_id'],
                    'source_model_id' => null,
                    'message' => 'No matching NHTSA brand exists in the local catalog.',
                ];
            } elseif ($mode !== 'brands') {
                foreach ($modelRows as $row) {
                    $model = $this->normalizer->model($row, $make['source_make_id']);
                    if ($model === null) {
                        $errors[] = [
                            'stage' => 'model',
                            'source_make_id' => $make['source_make_id'],
                            'source_model_id' => null,
                            'message' => 'NHTSA model row is incomplete or invalid.',
                        ];
                        continue;
                    }

                    if ($this->modelSourceExists($model['source_make_id'], $model['source_model_id'])) {
                        $duplicates++;
                        continue;
                    }
                    $existingModel = $this->findModelByName($brandId, $model['name']);
                    if ($existingModel !== null) {
                        $duplicates++;
                        if ($existingModel['source_model_id'] === null) {
                            $this->pdo->prepare(
                                "UPDATE vehicle_models
                                 SET source_make_id = :source_make_id, source_model_id = :source_model_id
                                 WHERE id = :id AND data_source = 'admin' AND source_model_id IS NULL"
                            )->execute([
                                'source_make_id' => $model['source_make_id'],
                                'source_model_id' => $model['source_model_id'],
                                'id' => $existingModel['id'],
                            ]);
                        } elseif (
                            (int) $existingModel['source_make_id'] !== $model['source_make_id']
                            || (int) $existingModel['source_model_id'] !== $model['source_model_id']
                        ) {
                            $errors[] = [
                                'stage' => 'model',
                                'source_make_id' => $model['source_make_id'],
                                'source_model_id' => $model['source_model_id'],
                                'message' => 'A local model with the same name is mapped to different NHTSA identifiers.',
                            ];
                        }
                        continue;
                    }

                    $insertModel = $this->pdo->prepare(
                        "INSERT INTO vehicle_models
                            (brand_id, name, year_from, year_to, image, status, data_source,
                             source_make_id, source_model_id, verification_status)
                         VALUES (:brand_id, :name, NULL, NULL, NULL, 1, 'nhtsa',
                                 :source_make_id, :source_model_id, 'unverified')"
                    );
                    $insertModel->execute([
                        'brand_id' => $brandId,
                        'name' => $model['name'],
                        'source_make_id' => $model['source_make_id'],
                        'source_model_id' => $model['source_model_id'],
                    ]);
                    $added++;
                }
            }

            foreach ($errors as $error) {
                $this->saveError(
                    $sessionId,
                    $error['stage'],
                    $error['source_make_id'],
                    $error['source_model_id'],
                    $error['message']
                );
            }

            $newCursor = $cursor + 1;
            $finished = $newCursor >= $total;
            $advance = $this->pdo->prepare(
                "UPDATE vehicle_import_sessions
                 SET next_cursor = :new_cursor, makes_processed = makes_processed + 1,
                     added_count = added_count + :added,
                     duplicate_count = duplicate_count + :duplicates,
                     error_count = error_count + :errors,
                     status = IF(
                         :finished_for_status = 1,
                         IF(error_count + :errors_for_status > 0, 'completed_with_errors', 'completed'),
                         'running'
                     ),
                     completed_at = IF(:finished = 1, CURRENT_TIMESTAMP, NULL),
                     last_error = NULL
                 WHERE id = :id AND next_cursor = :expected_cursor"
            );
            $advance->execute([
                'new_cursor' => $newCursor,
                'added' => $added,
                'duplicates' => $duplicates,
                'errors' => count($errors),
                'errors_for_status' => count($errors),
                'finished_for_status' => $finished ? 1 : 0,
                'finished' => $finished ? 1 : 0,
                'id' => $sessionId,
                'expected_cursor' => $cursor,
            ]);
            if ($advance->rowCount() !== 1) {
                throw new RuntimeException('Import session progress changed unexpectedly.');
            }

            $this->pdo->commit();

            return ['added' => $added, 'duplicates' => $duplicates, 'errors' => count($errors)];
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function findBrandBySourceId(int $sourceMakeId): ?int
    {
        $statement = $this->pdo->prepare(
            "SELECT id FROM vehicle_brands WHERE source_make_id = :source_make_id
             ORDER BY CASE data_source WHEN 'admin' THEN 0 ELSE 1 END, id LIMIT 1"
        );
        $statement->execute(['source_make_id' => $sourceMakeId]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    /**
     * @return array{id: int|string, source_make_id: int|string|null}|null
     */
    private function findBrandByName(string $name): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, source_make_id FROM vehicle_brands WHERE name = :name LIMIT 1'
        );
        $statement->execute(['name' => $name]);
        $brand = $statement->fetch();

        return $brand === false ? null : $brand;
    }

    private function modelSourceExists(int $sourceMakeId, int $sourceModelId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM vehicle_models
             WHERE source_make_id = :source_make_id
               AND source_model_id = :source_model_id
             LIMIT 1'
        );
        $statement->execute([
            'source_make_id' => $sourceMakeId,
            'source_model_id' => $sourceModelId,
        ]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * @return array{id: int|string, source_make_id: int|string|null, source_model_id: int|string|null}|null
     */
    private function findModelByName(int $brandId, string $name): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, source_make_id, source_model_id
             FROM vehicle_models WHERE brand_id = :brand_id AND name = :name LIMIT 1'
        );
        $statement->execute(['brand_id' => $brandId, 'name' => $name]);
        $model = $statement->fetch();

        return $model === false ? null : $model;
    }

    private function saveError(
        int $sessionId,
        string $stage,
        ?int $sourceMakeId,
        ?int $sourceModelId,
        string $message
    ): void {
        $statement = $this->pdo->prepare(
            'INSERT INTO vehicle_import_errors
                (session_id, stage, source_make_id, source_model_id, error_message)
             VALUES (:session_id, :stage, :source_make_id, :source_model_id, :message)'
        );
        $statement->execute([
            'session_id' => $sessionId,
            'stage' => $stage,
            'source_make_id' => $sourceMakeId,
            'source_model_id' => $sourceModelId,
            'message' => mb_substr($message, 0, 500, 'UTF-8'),
        ]);
    }
}
