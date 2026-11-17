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

    /**
     * @return array<string, mixed>|null
     */
    public function findCustomer(int $customerId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, full_name, email, phone, status
             FROM users
             WHERE id = :id AND role = 'customer'
             LIMIT 1"
        );
        $statement->execute(['id' => $customerId]);
        $customer = $statement->fetch();

        return $customer === false ? null : $customer;
    }

    /**
     * Tìm kiếm khách hàng theo tên, email, hoặc số điện thoại.
     * Lọc thêm theo trạng thái nếu được cung cấp.
     *
     * @return list<array<string, mixed>>
     */
    public function search(string $keyword = '', string $status = '', int $limit = 100): array
    {
        $limit = max(1, min($limit, 200));

        $conditions = ["u.role = 'customer'"];
        $params = [];

        if ($keyword !== '') {
            $conditions[] = '(u.full_name LIKE :name_kw OR u.email LIKE :email_kw OR u.phone LIKE :phone_kw)';
            $keywordPattern = '%' . $keyword . '%';
            $params['name_kw'] = $keywordPattern;
            $params['email_kw'] = $keywordPattern;
            $params['phone_kw'] = $keywordPattern;
        }

        if (in_array($status, ['active', 'inactive', 'locked'], true)) {
            $conditions[] = "u.status = :status";
            $params['status'] = $status;
        }

        $where = implode(' AND ', $conditions);

        $statement = $this->pdo->prepare(
            "SELECT u.id, u.full_name, u.email, u.phone, u.status, u.created_at,
                    COUNT(DISTINCT v.id) AS vehicle_count
             FROM users AS u
             LEFT JOIN vehicles AS v ON v.user_id = u.id
             WHERE {$where}
             GROUP BY u.id, u.full_name, u.email, u.phone, u.status, u.created_at
             ORDER BY u.created_at DESC, u.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

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

    /**
     * Lấy thông tin chi tiết khách hàng kèm danh sách xe.
     *
     * @return array<string, mixed>|null
     */
    public function findDetail(int $customerId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT u.id, u.full_name, u.email, u.phone, u.status, u.created_at, u.updated_at,
                    COUNT(DISTINCT v.id) AS vehicle_count,
                    COUNT(DISTINCT a.id) AS appointment_count
             FROM users AS u
             LEFT JOIN vehicles AS v ON v.user_id = u.id
             LEFT JOIN appointments AS a ON a.user_id = u.id
             WHERE u.id = :id AND u.role = 'customer'
             GROUP BY u.id, u.full_name, u.email, u.phone, u.status, u.created_at, u.updated_at
             LIMIT 1"
        );
        $statement->execute(['id' => $customerId]);
        $customer = $statement->fetch();

        return $customer === false ? null : $customer;
    }

    /**
     * Lấy danh sách xe của khách hàng.
     *
     * @return list<array<string, mixed>>
     */
    public function vehiclesOf(int $customerId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT v.id, v.user_id, v.license_plate, v.model, v.manufacture_year, v.color,
                    v.current_mileage, v.status, v.created_at,
                    b.name AS brand_name
             FROM vehicles AS v
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             WHERE v.user_id = :user_id
             ORDER BY v.created_at DESC"
        );
        $statement->execute(['user_id' => $customerId]);

        return $statement->fetchAll();
    }

    /**
     * Lấy lịch sử lịch hẹn gần nhất của khách hàng (tối đa 10).
     *
     * @return list<array<string, mixed>>
     */
    public function recentAppointmentsOf(int $customerId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT a.id, a.appointment_code, a.appointment_type,
                    a.appointment_date, a.appointment_time, a.status, a.created_at,
                    b.name AS brand_name, v.model, v.license_plate
             FROM appointments AS a
             INNER JOIN vehicles AS v ON v.id = a.vehicle_id
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             WHERE a.user_id = :user_id
             ORDER BY a.created_at DESC
             LIMIT 10"
        );
        $statement->execute(['user_id' => $customerId]);

        return $statement->fetchAll();
    }
}
