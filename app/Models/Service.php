<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Service
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     description: string|null,
     *     price: string,
     *     duration: int,
     *     image: string|null,
     *     category_name: string
     * }>
     */
    public function listActive(int $limit = 12): array
    {
        $limit = max(1, min($limit, 48));
        $statement = $this->pdo->query(
            "SELECT s.id, s.name, s.description, s.price, s.duration, s.image,
                    c.name AS category_name
             FROM services AS s
             INNER JOIN service_categories AS c ON c.id = s.category_id
             WHERE s.status = 'active' AND c.status = 1
             ORDER BY c.name, s.name
             LIMIT {$limit}"
        );

        return $statement->fetchAll();
    }
}
