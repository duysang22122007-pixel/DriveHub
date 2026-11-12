<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class VehicleModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $keyword = '', ?int $brandId = null): array
    {
        $conditions = [];
        $parameters = [];
        if ($keyword !== '') {
            $conditions[] = '(m.name LIKE :model_keyword OR b.name LIKE :brand_keyword)';
            $parameters['model_keyword'] = '%' . $keyword . '%';
            $parameters['brand_keyword'] = '%' . $keyword . '%';
        }
        if ($brandId !== null) {
            $conditions[] = 'm.brand_id = :brand_id';
            $parameters['brand_id'] = $brandId;
        }

        $sql = 'SELECT m.id, m.brand_id, b.name AS brand_name, m.name, m.year_from, m.year_to,
                       m.image, m.status
                FROM vehicle_models AS m
                INNER JOIN vehicle_brands AS b ON b.id = m.brand_id';
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY b.name, m.name';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    /**
     * @return list<array{id: int, name: string, year_from: ?int, year_to: ?int}>
     */
    public function activeForBrand(int $brandId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, year_from, year_to
             FROM vehicle_models
             WHERE brand_id = :brand_id AND status = 1
             ORDER BY name'
        );
        $statement->execute(['brand_id' => $brandId]);

        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $modelId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, brand_id, name, year_from, year_to, image, status
             FROM vehicle_models WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $modelId]);
        $model = $statement->fetch();

        return $model === false ? null : $model;
    }

    /**
     * @return array{id: int, brand_id: int, name: string}|null
     */
    public function findActiveForBrand(int $modelId, int $brandId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT m.id, m.brand_id, m.name
             FROM vehicle_models AS m
             INNER JOIN vehicle_brands AS b ON b.id = m.brand_id AND b.status = 1
             WHERE m.id = :id AND m.brand_id = :brand_id AND m.status = 1
             LIMIT 1'
        );
        $statement->execute(['id' => $modelId, 'brand_id' => $brandId]);
        $model = $statement->fetch();

        return $model === false ? null : $model;
    }

    public function create(int $brandId, string $name, ?int $yearFrom, ?int $yearTo, ?string $image): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO vehicle_models (brand_id, name, year_from, year_to, image, status)
             VALUES (:brand_id, :name, :year_from, :year_to, :image, 1)'
        );
        $statement->execute([
            'brand_id' => $brandId,
            'name' => $name,
            'year_from' => $yearFrom,
            'year_to' => $yearTo,
            'image' => $image,
        ]);
    }

    public function update(
        int $modelId,
        int $brandId,
        string $name,
        ?int $yearFrom,
        ?int $yearTo,
        ?string $image,
        int $status
    ): bool {
        $this->pdo->beginTransaction();

        try {
            $existing = $this->pdo->prepare(
                'SELECT brand_id, name FROM vehicle_models WHERE id = :id FOR UPDATE'
            );
            $existing->execute(['id' => $modelId]);
            $oldModel = $existing->fetch();
            if ($oldModel === false) {
                $this->pdo->commit();

                return false;
            }

            $update = $this->pdo->prepare(
                'UPDATE vehicle_models
                 SET brand_id = :brand_id, name = :name, year_from = :year_from,
                     year_to = :year_to, image = :image, status = :status
                 WHERE id = :id'
            );
            $update->execute([
                'brand_id' => $brandId,
                'name' => $name,
                'year_from' => $yearFrom,
                'year_to' => $yearTo,
                'image' => $image,
                'status' => $status,
                'id' => $modelId,
            ]);

            $syncVehicles = $this->pdo->prepare(
                'UPDATE vehicles
                 SET brand_id = :brand_id, model = :name, vehicle_model_id = :set_model_id
                 WHERE vehicle_model_id = :filter_model_id
                    OR (vehicle_model_id IS NULL AND brand_id = :old_brand_id AND model = :old_name)'
            );
            $syncVehicles->execute([
                'brand_id' => $brandId,
                'name' => $name,
                'set_model_id' => $modelId,
                'filter_model_id' => $modelId,
                'old_brand_id' => $oldModel['brand_id'],
                'old_name' => $oldModel['name'],
            ]);
            $this->pdo->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function updateStatus(int $modelId, int $status): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE vehicle_models SET status = :status WHERE id = :id'
        );
        $statement->execute(['status' => $status, 'id' => $modelId]);

        if ($statement->rowCount() > 0) {
            return true;
        }

        $check = $this->pdo->prepare('SELECT 1 FROM vehicle_models WHERE id = :id');
        $check->execute(['id' => $modelId]);

        return $check->fetchColumn() !== false;
    }

    public function deleteIfUnused(int $modelId): ?bool
    {
        $this->pdo->beginTransaction();

        try {
            $model = $this->pdo->prepare(
                'SELECT brand_id, name FROM vehicle_models WHERE id = :id FOR UPDATE'
            );
            $model->execute(['id' => $modelId]);
            $row = $model->fetch();
            if ($row === false) {
                $this->pdo->commit();

                return null;
            }

            $usage = $this->pdo->prepare(
                'SELECT 1 FROM vehicles
                 WHERE vehicle_model_id = :model_id
                    OR (vehicle_model_id IS NULL AND brand_id = :brand_id AND model = :name)
                 LIMIT 1'
            );
            $usage->execute([
                'model_id' => $modelId,
                'brand_id' => $row['brand_id'],
                'name' => $row['name'],
            ]);
            if ($usage->fetchColumn() !== false) {
                $this->pdo->commit();

                return false;
            }

            $delete = $this->pdo->prepare('DELETE FROM vehicle_models WHERE id = :id');
            $delete->execute(['id' => $modelId]);
            $this->pdo->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
