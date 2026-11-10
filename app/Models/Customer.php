<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Customer
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));
        $statement = $this->pdo->query(
            "SELECT u.id, u.full_name, u.email, u.phone, u.status, u.created_at,
                    COUNT(DISTINCT v.id) AS vehicle_count
             FROM users AS u
             LEFT JOIN vehicles AS v ON v.user_id = u.id
             WHERE u.role = 'customer'
             GROUP BY u.id, u.full_name, u.email, u.phone, u.status, u.created_at
             ORDER BY u.created_at DESC, u.id DESC
             LIMIT {$limit}"
        );

        return $statement->fetchAll();
    }

    public function updateStatus(int $customerId, string $status): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE users SET status = :status WHERE id = :id AND role = 'customer'"
        );
        $statement->execute(['status' => $status, 'id' => $customerId]);

        return $statement->rowCount() > 0;
    }
}
