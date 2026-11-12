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
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function reviewPage(
        string $keyword,
        ?int $brandId,
        ?string $verificationStatus,
        int $page,
        int $pageSize = 25
    ): array {
        $conditions = ["m.data_source = 'nhtsa'"];
        $parameters = [];
        if ($keyword !== '') {
            $conditions[] = 'm.name LIKE :keyword';
            $parameters['keyword'] = '%' . $keyword . '%';
        }
        if ($brandId !== null) {
            $conditions[] = 'm.brand_id = :brand_id';
            $parameters['brand_id'] = $brandId;
        }
        if ($verificationStatus !== null) {
            $conditions[] = 'm.verification_status = :verification_status';
            $parameters['verification_status'] = $verificationStatus;
        }
        $where = implode(' AND ', $conditions);

        $count = $this->pdo->prepare(
            'SELECT COUNT(*) FROM vehicle_models AS m WHERE ' . $where
        );
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();

        $statement = $this->pdo->prepare(
            'SELECT m.id, m.brand_id, b.name AS brand_name, m.name, m.year_from, m.year_to,
                    m.image, m.status, m.data_source, m.source_make_id, m.source_model_id,
                    m.verification_status, m.reviewed_by, m.reviewed_at,
                    reviewer.full_name AS reviewer_name
             FROM vehicle_models AS m
             INNER JOIN vehicle_brands AS b ON b.id = m.brand_id
             LEFT JOIN users AS reviewer ON reviewer.id = m.reviewed_by
             WHERE ' . $where . '
             ORDER BY CASE m.verification_status
                          WHEN \'unverified\' THEN 0
                          WHEN \'verified\' THEN 1
                          ELSE 2
                      END,
                      b.name, m.name, m.id
             LIMIT :limit OFFSET :offset'
        );
        foreach ($parameters as $name => $value) {
            $statement->bindValue($name, $value);
        }
        $statement->bindValue('limit', max(1, min($pageSize, 100)), PDO::PARAM_INT);
        $statement->bindValue('offset', max(0, ($page - 1) * $pageSize), PDO::PARAM_INT);
        $statement->execute();

        return ['items' => $statement->fetchAll(), 'total' => $total];
    }

    /**
     * @return list<array{id: int, name: string, year_from: ?int, year_to: ?int}>
     */
    public function activeForBrand(int $brandId, string $keyword = ''): array
    {
        $sql = 'SELECT id, name, year_from, year_to
                FROM vehicle_models
                WHERE brand_id = :brand_id AND status = 1 AND verification_status = \'verified\'';
        $parameters = ['brand_id' => $brandId];
        if ($keyword !== '') {
            $sql .= " AND name LIKE :keyword ESCAPE '='";
            $escapedKeyword = str_replace(['=', '%', '_'], ['==', '=%', '=_'], $keyword);
            $parameters['keyword'] = '%' . $escapedKeyword . '%';
        }
        $sql .= ' ORDER BY name LIMIT 100';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

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
            "SELECT m.id, m.brand_id, m.name
             FROM vehicle_models AS m
             INNER JOIN vehicle_brands AS b ON b.id = m.brand_id AND b.status = 1
             WHERE m.id = :id AND m.brand_id = :brand_id AND m.status = 1
               AND m.verification_status = 'verified'
             LIMIT 1"
        );
        $statement->execute(['id' => $modelId, 'brand_id' => $brandId]);
        $model = $statement->fetch();

        return $model === false ? null : $model;
    }

    public function create(
        int $brandId,
        string $name,
        ?int $yearFrom,
        ?int $yearTo,
        ?string $image,
        int $reviewedBy
    ): void {
        $statement = $this->pdo->prepare(
            "INSERT INTO vehicle_models
                (brand_id, name, year_from, year_to, image, status, data_source,
                 verification_status, reviewed_by, reviewed_at)
             VALUES (:brand_id, :name, :year_from, :year_to, :image, 1, 'admin',
                     'verified', :reviewed_by, CURRENT_TIMESTAMP)"
        );
        $statement->execute([
            'brand_id' => $brandId,
            'name' => $name,
            'year_from' => $yearFrom,
            'year_to' => $yearTo,
            'image' => $image,
            'reviewed_by' => $reviewedBy,
        ]);
    }

    public function setVerificationStatus(int $modelId, string $status, int $reviewedBy): bool
    {
        if (!in_array($status, ['verified', 'not_suitable'], true)) {
            throw new \InvalidArgumentException('Unsupported vehicle-model review status.');
        }

        $statement = $this->pdo->prepare(
            "UPDATE vehicle_models
             SET verification_status = :status, reviewed_by = :reviewed_by,
                 reviewed_at = CURRENT_TIMESTAMP
             WHERE id = :id AND data_source = 'nhtsa'"
        );
        $statement->execute([
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'id' => $modelId,
        ]);
        if ($statement->rowCount() > 0) {
            return true;
        }

        $check = $this->pdo->prepare(
            "SELECT 1 FROM vehicle_models WHERE id = :id AND data_source = 'nhtsa'"
        );
        $check->execute(['id' => $modelId]);

        return $check->fetchColumn() !== false;
    }

    /**
     * @param list<int> $modelIds
     */
    public function setVerificationStatusForMany(array $modelIds, string $status, int $reviewedBy): int
    {
        if ($modelIds === [] || !in_array($status, ['verified', 'not_suitable'], true)) {
            throw new \InvalidArgumentException('Invalid vehicle-model review selection.');
        }

        $placeholders = [];
        $parameters = [
            'status' => $status,
            'reviewed_by' => $reviewedBy,
        ];
        foreach (array_values(array_unique($modelIds)) as $index => $modelId) {
            $placeholder = 'model_id_' . $index;
            $placeholders[] = ':' . $placeholder;
            $parameters[$placeholder] = $modelId;
        }

        $ownsTransaction = !$this->pdo->inTransaction();
        try {
            if ($ownsTransaction) {
                $this->pdo->beginTransaction();
            }
            $matching = $this->pdo->prepare(
                "SELECT COUNT(*) FROM vehicle_models
                 WHERE data_source = 'nhtsa' AND id IN (" . implode(', ', $placeholders) . ')'
            );
            $matching->execute(array_diff_key($parameters, ['status' => true, 'reviewed_by' => true]));
            $matchedCount = (int) $matching->fetchColumn();

            $statement = $this->pdo->prepare(
                "UPDATE vehicle_models
                 SET verification_status = :status, reviewed_by = :reviewed_by,
                     reviewed_at = CURRENT_TIMESTAMP
                 WHERE data_source = 'nhtsa' AND id IN (" . implode(', ', $placeholders) . ')'
            );
            $statement->execute($parameters);
            if ($ownsTransaction) {
                $this->pdo->commit();
            }

            return $matchedCount;
        } catch (\Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
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
