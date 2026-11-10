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
     * @return list<array{id: int, name: string, status: int}>
     */
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, name, status FROM vehicle_brands ORDER BY name'
        );

        return $statement->fetchAll();
    }

    public function create(string $name): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO vehicle_brands (name, status) VALUES (:name, 1)'
        );
        $statement->execute(['name' => $name]);
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
}
