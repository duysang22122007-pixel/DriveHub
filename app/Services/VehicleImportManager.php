<?php

declare(strict_types=1);

namespace App\Services;

use JsonException;
use PDO;
use RuntimeException;
use Throwable;

final class VehicleImportManager
{
    private const MAKES_PER_REQUEST = 5;

    public function __construct(
        private readonly PDO $pdo,
        private readonly NhtsaClient $client,
        private readonly VehicleDataNormalizer $normalizer,
        private readonly VehicleCatalogImporter $importer
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function start(string $mode, ?int $adminId, bool $includeTrucks = false): array
    {
        if (!in_array($mode, ['brands', 'models', 'all'], true)) {
            throw new RuntimeException('Loại đồng bộ không hợp lệ.');
        }

        $makes = [];
        if ($mode === 'models') {
            try {
                $eligibleMakes = $this->eligibleMakes($includeTrucks);
            } catch (RuntimeException $exception) {
                $this->recordFailedStart($mode, $adminId, $exception->getMessage());

                throw $exception;
            }
            $eligibleMakeIds = array_fill_keys(array_column($eligibleMakes, 'source_make_id'), true);
            $statement = $this->pdo->query(
                "SELECT source_make_id, name FROM vehicle_brands
                 WHERE source_make_id IS NOT NULL
                 ORDER BY id"
            );
            foreach ($statement->fetchAll() as $brand) {
                $sourceMakeId = (int) $brand['source_make_id'];
                if (!isset($eligibleMakeIds[$sourceMakeId])) {
                    continue;
                }
                $makes[] = [
                    'source_make_id' => $sourceMakeId,
                    'name' => (string) $brand['name'],
                ];
            }
            if ($makes === []) {
                throw new RuntimeException('Không có hãng đã nhập nào thuộc loại phương tiện được chọn.');
            }
        } else {
            try {
                $makes = $this->eligibleMakes($includeTrucks);
            } catch (RuntimeException $exception) {
                $this->recordFailedStart($mode, $adminId, $exception->getMessage());

                throw $exception;
            }
        }

        try {
            $payload = json_encode($makes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to store the NHTSA make list.', 0, $exception);
        }

        $this->pdo->beginTransaction();
        try {
            $create = $this->pdo->prepare(
                "INSERT INTO vehicle_import_sessions
                    (mode, status, makes_payload, makes_total, error_count, created_by)
                 VALUES (:mode, 'queued', :payload, :total, :errors, :created_by)"
            );
            $create->execute([
                'mode' => $mode,
                'payload' => $payload,
                'total' => count($makes),
                'errors' => 0,
                'created_by' => $adminId,
            ]);
            $sessionId = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

        if ($makes === []) {
            $complete = $this->pdo->prepare(
                'UPDATE vehicle_import_sessions SET status = :status, completed_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $complete->execute(['status' => 'completed', 'id' => $sessionId]);
        }

        $session = $this->findSession($sessionId);
        $limit = $session !== null && $session['mode'] === 'brands' ? self::MAKES_PER_REQUEST : 1;

        return $this->process($sessionId, $limit);
    }

    /**
     * NHTSA vehicle-type endpoints classify makes, not individual models.
     *
     * @return list<array{source_make_id: int, name: string}>
     */
    private function eligibleMakes(bool $includeTrucks): array
    {
        $types = ['car', 'mpv'];
        if ($includeTrucks) {
            $types[] = 'truck';
        }

        $makesById = [];
        foreach ($types as $type) {
            foreach ($this->client->getMakesForVehicleType($type) as $row) {
                $make = $this->normalizer->make($row);
                if ($make !== null) {
                    $makesById[$make['source_make_id']] = $make;
                }
            }
        }

        $makes = array_values($makesById);
        usort($makes, static fn (array $left, array $right): int => strcasecmp($left['name'], $right['name']));

        return $makes;
    }

    /**
     * @return array<string, mixed>
     */
    public function resume(int $sessionId, int $limit = self::MAKES_PER_REQUEST): array
    {
        if ($sessionId < 1 || $limit < 1 || $limit > self::MAKES_PER_REQUEST) {
            throw new RuntimeException('Yêu cầu tiếp tục phiên đồng bộ không hợp lệ.');
        }

        $session = $this->findSession($sessionId);
        if ($session === null) {
            throw new RuntimeException('Không tìm thấy phiên đồng bộ.');
        }
        if ($session['status'] === 'failed') {
            throw new RuntimeException('Không thể tiếp tục: lần gọi lấy danh sách hãng đã thất bại. Hãy bắt đầu phiên mới.');
        }

        return $this->process($sessionId, $session['mode'] === 'brands' ? $limit : 1);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function history(int $limit = 25): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, mode, status, makes_total, makes_processed, added_count,
                    duplicate_count, error_count, last_error,
                    (SELECT e.error_message FROM vehicle_import_errors e
                     WHERE e.session_id = vehicle_import_sessions.id ORDER BY e.id DESC LIMIT 1) AS latest_detail,
                    created_at, updated_at, completed_at
             FROM vehicle_import_sessions ORDER BY id DESC LIMIT :limit'
        );
        $statement->bindValue('limit', max(1, min($limit, 100)), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>
     */
    private function process(int $sessionId, int $limit): array
    {
        $session = $this->findSession($sessionId);
        if ($session === null) {
            throw new RuntimeException('Không tìm thấy phiên đồng bộ.');
        }
        if (in_array($session['status'], ['completed', 'completed_with_errors'], true)) {
            return $session;
        }

        try {
            $makes = json_decode((string) $session['makes_payload'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Dữ liệu tiến trình đồng bộ bị lỗi.', 0, $exception);
        }
        if (!is_array($makes)) {
            throw new RuntimeException('Dữ liệu tiến trình đồng bộ không hợp lệ.');
        }

        $this->setRunning($sessionId);
        $processedThisRequest = 0;
        while ((int) $session['next_cursor'] < count($makes) && $processedThisRequest < $limit) {
            $cursor = (int) $session['next_cursor'];
            $make = $makes[$cursor] ?? null;
            if (
                !is_array($make)
                || !isset($make['source_make_id'], $make['name'])
                || !is_int($make['source_make_id'])
                || !is_string($make['name'])
            ) {
                $this->recordApiError($sessionId, null, 'Stored make data is invalid.');
                break;
            }

            try {
                $modelRows = $session['mode'] === 'brands'
                    ? []
                    : $this->client->getModelsForMake($make['source_make_id']);
                $this->importer->importMake(
                    $sessionId,
                    $make,
                    $modelRows,
                    (string) $session['mode'],
                    $cursor,
                    count($makes)
                );
            } catch (RuntimeException $exception) {
                $this->recordApiError(
                    $sessionId,
                    $make['source_make_id'],
                    'NHTSA request/import failed: ' . $exception->getMessage()
                );
                break;
            }

            $processedThisRequest++;
            $session = $this->findSession($sessionId);
            if ($session === null) {
                throw new RuntimeException('Import session disappeared during processing.');
            }
        }

        $result = $this->findSession($sessionId);
        if ($result === null) {
            throw new RuntimeException('Import session disappeared after processing.');
        }

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findSession(int $sessionId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM vehicle_import_sessions WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $sessionId]);
        $session = $statement->fetch();

        return $session === false ? null : $session;
    }

    private function setRunning(int $sessionId): void
    {
        $statement = $this->pdo->prepare(
            "UPDATE vehicle_import_sessions
             SET status = 'running', last_error = NULL, completed_at = NULL
             WHERE id = :id AND status NOT IN ('completed', 'completed_with_errors')"
        );
        $statement->execute(['id' => $sessionId]);
    }

    private function recordApiError(int $sessionId, ?int $makeId, string $message): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->saveError($sessionId, 'request', $makeId, null, $message);
            $update = $this->pdo->prepare(
                "UPDATE vehicle_import_sessions
                 SET error_count = error_count + 1, status = 'running', last_error = :error
                 WHERE id = :id"
            );
            $update->execute([
                'error' => mb_substr($message, 0, 500, 'UTF-8'),
                'id' => $sessionId,
            ]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function recordFailedStart(string $mode, ?int $adminId, string $message): void
    {
        $this->pdo->beginTransaction();
        try {
            $create = $this->pdo->prepare(
                "INSERT INTO vehicle_import_sessions
                    (mode, status, makes_payload, error_count, last_error, created_by, completed_at)
                 VALUES (:mode, 'failed', '[]', 1, :error, :created_by, CURRENT_TIMESTAMP)"
            );
            $create->execute([
                'mode' => $mode,
                'error' => mb_substr($message, 0, 500, 'UTF-8'),
                'created_by' => $adminId,
            ]);
            $this->saveError((int) $this->pdo->lastInsertId(), 'request', null, null, $message);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
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
