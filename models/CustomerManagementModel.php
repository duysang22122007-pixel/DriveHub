<?php

class CustomerManagementModel extends BaseModel
{
    public function getAll(string $search = ''): array
    {
        $sql = 'SELECT c.id AS customer_id, c.full_name, c.phone, c.address,
                       u.id AS user_id, u.username, u.email, u.status, u.created_at
                FROM CUSTOMERS c
                JOIN USERS u ON u.id = c.user_id
                JOIN ROLES r ON r.id = u.role_id AND r.name = \'customer\'';
        $parameters = [];

        if ($search !== '') {
            $sql .= ' WHERE c.full_name LIKE :name OR u.email LIKE :email OR c.phone LIKE :phone';
            $term = '%' . $search . '%';
            $parameters = ['name' => $term, 'email' => $term, 'phone' => $term];
        }

        $sql .= ' ORDER BY c.full_name, c.id';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function getById(int $customerId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.id AS customer_id, c.full_name, c.phone, c.address,
                    u.id AS user_id, u.username, u.email, u.status, u.created_at
             FROM CUSTOMERS c
             JOIN USERS u ON u.id = c.user_id
             JOIN ROLES r ON r.id = u.role_id AND r.name = \'customer\'
             WHERE c.id = :customer_id
             LIMIT 1'
        );
        $statement->execute(['customer_id' => $customerId]);
        $customer = $statement->fetch();

        return $customer ?: null;
    }

    public function getCars(int $customerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.id, c.license_plate, c.model, c.manufacture_year, c.current_mileage,
                    b.name AS brand
             FROM CARS c
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             WHERE c.customer_id = :customer_id
             ORDER BY c.license_plate'
        );
        $statement->execute(['customer_id' => $customerId]);

        return $statement->fetchAll();
    }

    public function getRepairHistory(int $customerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT ro.id, ro.received_date, ro.status, ro.problem_description, ro.diagnosis,
                    c.license_plate, c.model AS car_model, b.name AS car_brand,
                    (SELECT GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR \', \')
                     FROM REPAIR_DETAILS rd
                     JOIN SERVICES s ON s.id = rd.service_id
                     WHERE rd.repair_order_id = ro.id) AS service_names
             FROM REPAIR_ORDERS ro
             JOIN CARS c ON c.id = ro.car_id
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             WHERE c.customer_id = :customer_id
             ORDER BY ro.received_date DESC, ro.id DESC'
        );
        $statement->execute(['customer_id' => $customerId]);

        return $statement->fetchAll();
    }

    public function setStatus(int $customerId, int $status): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE USERS u
             JOIN CUSTOMERS c ON c.user_id = u.id
             JOIN ROLES r ON r.id = u.role_id AND r.name = \'customer\'
             SET u.status = :status
             WHERE c.id = :customer_id'
        );
        $statement->execute(['status' => $status, 'customer_id' => $customerId]);

        if ($statement->rowCount() === 0 && $this->getById($customerId) === null) {
            throw new DomainException('Không tìm thấy tài khoản khách hàng.');
        }
    }
}
