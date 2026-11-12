<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class VehicleBrand
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array{id: int, name: string, logo: ?string, status: int}>
     */
    public function search(string $keyword = ''): array
    {
        $sql = 'SELECT id, name, logo, status FROM vehicle_brands';
        $parameters = [];
        if ($keyword !== '') {
            $sql .= ' WHERE name LIKE :keyword';
            $parameters['keyword'] = '%' . $keyword . '%';
        }
        $sql .= ' ORDER BY name';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    /**
     * @return array{id: int, name: string, logo: ?string, status: int}|null
     */
    public function find(int $brandId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, logo, status FROM vehicle_brands WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $brandId]);
        $brand = $statement->fetch();

        return $brand === false ? null : $brand;
    }

    public function create(string $name, ?string $logo): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO vehicle_brands (name, logo, status) VALUES (:name, :logo, 1)'
        );
        $statement->execute(['name' => $name, 'logo' => $logo]);
    }

    public function exists(int $brandId, bool $activeOnly = false): bool
    {
        $sql = 'SELECT 1 FROM vehicle_brands WHERE id = :id';
        if ($activeOnly) {
            $sql .= ' AND status = 1';
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $brandId]);

        return $statement->fetchColumn() !== false;
    }

    public function update(int $brandId, string $name, ?string $logo, int $status): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE vehicle_brands SET name = :name, logo = :logo, status = :status WHERE id = :id'
        );
        $statement->execute([
            'name' => $name,
            'logo' => $logo,
            'status' => $status,
            'id' => $brandId,
        ]);

        if ($statement->rowCount() > 0) {
            return true;
        }

        $check = $this->pdo->prepare('SELECT 1 FROM vehicle_brands WHERE id = :id');
        $check->execute(['id' => $brandId]);

        return $check->fetchColumn() !== false;
    }

    public function updateStatus(int $brandId, int $status): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE vehicle_brands SET status = :status WHERE id = :id'
        );
        $statement->execute(['status' => $status, 'id' => $brandId]);

        if ($statement->rowCount() > 0) {
            return true;
        }

        $check = $this->pdo->prepare('SELECT 1 FROM vehicle_brands WHERE id = :id');
        $check->execute(['id' => $brandId]);

        return $check->fetchColumn() !== false;
    }

    public function deleteIfUnused(int $brandId): ?bool
    {
        $this->pdo->beginTransaction();

        try {
            $brand = $this->pdo->prepare('SELECT id FROM vehicle_brands WHERE id = :id FOR UPDATE');
            $brand->execute(['id' => $brandId]);
            if ($brand->fetchColumn() === false) {
                $this->pdo->commit();

                return null;
            }

            $usage = $this->pdo->prepare(
                'SELECT (
                    EXISTS (SELECT 1 FROM vehicles WHERE brand_id = :vehicle_brand_id)
                    OR EXISTS (SELECT 1 FROM vehicle_models WHERE brand_id = :model_brand_id)
                )'
            );
            $usage->execute([
                'vehicle_brand_id' => $brandId,
                'model_brand_id' => $brandId,
            ]);
            if ((int) $usage->fetchColumn() === 1) {
                $this->pdo->commit();

                return false;
            }

            $delete = $this->pdo->prepare('DELETE FROM vehicle_brands WHERE id = :id');
            $delete->execute(['id' => $brandId]);
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
