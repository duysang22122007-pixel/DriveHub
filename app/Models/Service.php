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

    /**
     * @return list<array{id: int, name: string, description: string|null, status: int, service_count: int}>
     */
    public function adminCategories(): array
    {
        $statement = $this->pdo->query(
            'SELECT c.id, c.name, c.description, c.status, COUNT(s.id) AS service_count
             FROM service_categories AS c
             LEFT JOIN services AS s ON s.category_id = c.id
             GROUP BY c.id, c.name, c.description, c.status
             ORDER BY c.id'
        );

        return $statement->fetchAll();
    }

    /**
     * @return list<array{
     *     id: int,
     *     category_id: int,
     *     category_name: string,
     *     name: string,
     *     description: string|null,
     *     price: string,
     *     duration: int,
     *     status: string
     * }>
     */
    public function adminServices(): array
    {
        $statement = $this->pdo->query(
            'SELECT s.id, s.category_id, c.name AS category_name, s.name, s.description,
                    s.price, s.duration, s.status
             FROM services AS s
             INNER JOIN service_categories AS c ON c.id = s.category_id
             ORDER BY c.id, s.name, s.id'
        );

        return $statement->fetchAll();
    }

    public function saveCategory(?int $categoryId, string $name, ?string $description): void
    {
        if ($categoryId === null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO service_categories (name, description, status)
                 VALUES (:name, :description, 1)'
            );
            $statement->execute(['name' => $name, 'description' => $description]);

            return;
        }

        $statement = $this->pdo->prepare(
            'UPDATE service_categories
             SET name = :name, description = :description
             WHERE id = :id'
        );
        $statement->execute([
            'name' => $name,
            'description' => $description,
            'id' => $categoryId,
        ]);
        if ($statement->rowCount() === 0 && !$this->categoryExists($categoryId)) {
            throw new \InvalidArgumentException('Không tìm thấy nhóm dịch vụ cần cập nhật.');
        }
    }

    public function deleteCategoryIfEmpty(int $categoryId): void
    {
        $this->pdo->beginTransaction();
        try {
            $category = $this->pdo->prepare(
                'SELECT id FROM service_categories WHERE id = :id FOR UPDATE'
            );
            $category->execute(['id' => $categoryId]);
            if ($category->fetchColumn() === false) {
                throw new \InvalidArgumentException('Không tìm thấy nhóm dịch vụ cần xóa.');
            }

            $services = $this->pdo->prepare(
                'SELECT 1 FROM services WHERE category_id = :category_id LIMIT 1 FOR UPDATE'
            );
            $services->execute(['category_id' => $categoryId]);
            if ($services->fetchColumn() !== false) {
                throw new \InvalidArgumentException(
                    'Nhóm còn dịch vụ. Hãy chuyển hoặc ngừng các dịch vụ trước khi xóa nhóm.'
                );
            }

            $delete = $this->pdo->prepare('DELETE FROM service_categories WHERE id = :id');
            $delete->execute(['id' => $categoryId]);
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function saveService(
        ?int $serviceId,
        int $categoryId,
        string $name,
        ?string $description,
        string $price,
        int $duration,
        string $status
    ): void {
        if (!$this->categoryExists($categoryId, true)) {
            throw new \InvalidArgumentException('Nhóm dịch vụ không còn hoạt động.');
        }

        if ($serviceId === null) {
            $statement = $this->pdo->prepare(
                'INSERT INTO services (category_id, name, description, price, duration, status)
                 VALUES (:category_id, :name, :description, :price, :duration, :status)'
            );
            $statement->execute([
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'duration' => $duration,
                'status' => $status,
            ]);

            return;
        }

        $statement = $this->pdo->prepare(
            'UPDATE services
             SET category_id = :category_id, name = :name, description = :description,
                 price = :price, duration = :duration, status = :status
             WHERE id = :id'
        );
        $statement->execute([
            'category_id' => $categoryId,
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'duration' => $duration,
            'status' => $status,
            'id' => $serviceId,
        ]);
        if ($statement->rowCount() === 0 && !$this->serviceExists($serviceId)) {
            throw new \InvalidArgumentException('Không tìm thấy dịch vụ cần cập nhật.');
        }
    }

    /**
     * Dịch vụ được ngừng thay vì xóa vật lý để bảo toàn lịch sử đặt dịch vụ.
     */
    public function deactivateService(int $serviceId): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE services SET status = 'inactive' WHERE id = :id"
        );
        $statement->execute(['id' => $serviceId]);

        return $statement->rowCount() > 0 || $this->serviceExists($serviceId);
    }

    /**
     * @param list<int> $serviceIds
     */
    public function moveServices(array $serviceIds, int $categoryId): void
    {
        if (!$this->categoryExists($categoryId, true)) {
            throw new \InvalidArgumentException('Nhóm đích không còn hoạt động.');
        }
        if ($serviceIds === []) {
            throw new \InvalidArgumentException('Hãy chọn ít nhất một dịch vụ cần chuyển nhóm.');
        }

        $placeholders = [];
        $parameters = ['category_id' => $categoryId];
        foreach (array_values(array_unique($serviceIds)) as $index => $serviceId) {
            $key = 'service_id_' . $index;
            $placeholders[] = ':' . $key;
            $parameters[$key] = $serviceId;
        }

        $this->pdo->beginTransaction();
        try {
            $select = $this->pdo->prepare(
                'SELECT id FROM services WHERE id IN (' . implode(', ', $placeholders) . ') FOR UPDATE'
            );
            $select->execute(array_diff_key($parameters, ['category_id' => true]));
            if (count($select->fetchAll(PDO::FETCH_COLUMN)) !== count(array_unique($serviceIds))) {
                throw new \InvalidArgumentException('Một hoặc nhiều dịch vụ không còn tồn tại.');
            }

            $update = $this->pdo->prepare(
                'UPDATE services SET category_id = :category_id
                 WHERE id IN (' . implode(', ', $placeholders) . ')'
            );
            $update->execute($parameters);
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function categoryExists(int $categoryId, bool $activeOnly = false): bool
    {
        $sql = 'SELECT 1 FROM service_categories WHERE id = :id';
        if ($activeOnly) {
            $sql .= ' AND status = 1';
        }
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['id' => $categoryId]);

        return $statement->fetchColumn() !== false;
    }

    private function serviceExists(int $serviceId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM services WHERE id = :id');
        $statement->execute(['id' => $serviceId]);

        return $statement->fetchColumn() !== false;
    }
}
