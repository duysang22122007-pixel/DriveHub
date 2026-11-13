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

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     description: string|null,
     *     service_count: int
     * }>
     */
    public function listActiveCategories(): array
    {
        $statement = $this->pdo->query(
            "SELECT c.id, c.name, c.description, COUNT(s.id) AS service_count
             FROM service_categories AS c
             LEFT JOIN services AS s ON s.category_id = c.id AND s.status = 'active'
             WHERE c.status = 1
             GROUP BY c.id, c.name, c.description
             ORDER BY c.id"
        );

        return $statement->fetchAll();
    }

    /**
     * @return list<array{
     *     id: int,
     *     category_id: int,
     *     name: string,
     *     description: string|null,
     *     price: string,
     *     duration: int,
     *     image: string|null,
     *     category_name: string
     * }>
     */
    public function searchActive(string $keyword = '', ?int $categoryId = null): array
    {
        $sql = "SELECT s.id, s.category_id, s.name, s.description, s.price, s.duration, s.image,
                       c.name AS category_name
                FROM services AS s
                INNER JOIN service_categories AS c ON c.id = s.category_id
                WHERE s.status = 'active' AND c.status = 1";
        $parameters = [];

        if ($categoryId !== null && $categoryId > 0) {
            $sql .= ' AND s.category_id = :category_id';
            $parameters['category_id'] = $categoryId;
        }

        $keyword = trim($keyword);
        if ($keyword !== '') {
            $sql .= ' AND (s.name LIKE :keyword_name OR s.description LIKE :keyword_description)';
            $parameters['keyword_name'] = '%' . $keyword . '%';
            $parameters['keyword_description'] = '%' . $keyword . '%';
        }

        $sql .= ' ORDER BY c.id, s.name';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    /**
     * @return array{
     *     id: int,
     *     category_id: int,
     *     name: string,
     *     description: string|null,
     *     price: string,
     *     duration: int,
     *     image: string|null,
     *     category_name: string
     * }|null
     */
    public function findActiveById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT s.id, s.category_id, s.name, s.description, s.price, s.duration, s.image,
                    c.name AS category_name
             FROM services AS s
             INNER JOIN service_categories AS c ON c.id = s.category_id
             WHERE s.id = :id AND s.status = 'active' AND c.status = 1
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $service = $statement->fetch();

        return $service === false ? null : $service;
    }

    /**
     * @return list<array{
     *     id: int,
     *     category_id: int,
     *     name: string,
     *     description: string|null,
     *     price: string,
     *     duration: int,
     *     image: string|null,
     *     category_name: string
     * }>
     */
    public function relatedActive(int $categoryId, int $excludeId, int $limit = 3): array
    {
        $limit = max(1, min($limit, 12));
        $statement = $this->pdo->prepare(
            "SELECT s.id, s.category_id, s.name, s.description, s.price, s.duration, s.image,
                    c.name AS category_name
             FROM services AS s
             INNER JOIN service_categories AS c ON c.id = s.category_id
             WHERE s.category_id = :category_id
               AND s.id <> :exclude_id
               AND s.status = 'active' AND c.status = 1
             ORDER BY s.name
             LIMIT {$limit}"
        );
        $statement->execute([
            'category_id' => $categoryId,
            'exclude_id' => $excludeId,
        ]);

        return $statement->fetchAll();
    }

    /**
     * @return list<array{
     *     id: int,
     *     category_id: int,
     *     name: string,
     *     description: string|null,
     *     price: string,
     *     duration: int,
     *     image: string|null,
     *     category_name: string
     * }>
     */
    public function listFeatured(int $limit = 6): array
    {
        $limit = max(1, min($limit, 12));
        $statement = $this->pdo->query(
            "SELECT s.id, s.category_id, s.name, s.description, s.price, s.duration, s.image,
                    c.name AS category_name
             FROM services AS s
             INNER JOIN service_categories AS c ON c.id = s.category_id
             WHERE s.status = 'active' AND c.status = 1
               AND s.id = (
                   SELECT MIN(s2.id)
                   FROM services AS s2
                   WHERE s2.category_id = s.category_id AND s2.status = 'active'
               )
             ORDER BY c.id
             LIMIT {$limit}"
        );

        return $statement->fetchAll();
    }
}
