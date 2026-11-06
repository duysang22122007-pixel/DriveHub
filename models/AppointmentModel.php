<?php

class AppointmentModel extends BaseModel
{
    private const UPCOMING_STATUSES = ['pending', 'confirmed', 'checked_in'];
    private const HISTORY_STATUSES = ['completed', 'cancelled'];

    public function getUpcomingAppointments(int $customerId): array
    {
        return $this->getByCustomerAndStatuses($customerId, self::UPCOMING_STATUSES);
    }

    public function getHistoryAppointments(int $customerId): array
    {
        return $this->getByCustomerAndStatuses($customerId, self::HISTORY_STATUSES);
    }

    private function getByCustomerAndStatuses(int $customerId, array $statuses): array
    {
        $placeholders = implode(', ', array_fill(0, count($statuses), '?'));
        $dateOrder = $statuses === self::UPCOMING_STATUSES ? 'ASC' : 'DESC';
        $statement = $this->pdo->prepare(
            "SELECT a.*, c.license_plate, c.model AS car_model, b.name AS car_brand,
                    s.name AS service_name, ro.id AS repair_order_id, ro.status AS repair_status
             FROM APPOINTMENTS a
             JOIN CARS c ON c.id = a.car_id
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             JOIN SERVICES s ON s.id = a.service_id
             LEFT JOIN REPAIR_ORDERS ro ON ro.appointment_id = a.id
             WHERE a.customer_id = ? AND a.status IN ($placeholders)
             ORDER BY a.appointment_date $dateOrder"
        );
        $statement->execute(array_merge([$customerId], $statuses));

        return $statement->fetchAll();
    }

    public function getForAdmin(): array
    {
        return $this->pdo->query(
            "SELECT a.*, c.license_plate, c.model AS car_model, b.name AS car_brand,
                    cu.full_name AS customer_name, cu.phone AS customer_phone,
                    s.name AS service_name, ro.id AS repair_order_id
             FROM APPOINTMENTS a
             JOIN CARS c ON c.id = a.car_id
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             JOIN CUSTOMERS cu ON cu.id = a.customer_id
             JOIN SERVICES s ON s.id = a.service_id
             LEFT JOIN REPAIR_ORDERS ro ON ro.appointment_id = a.id
             ORDER BY FIELD(a.status, 'pending', 'confirmed', 'checked_in', 'completed', 'cancelled'),
                      a.appointment_date ASC"
        )->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.*, c.license_plate, c.model AS car_model, c.current_mileage,
                    b.name AS car_brand, cu.full_name AS customer_name, cu.phone AS customer_phone,
                    s.name AS service_name, s.id AS selected_service_id
             FROM APPOINTMENTS a
             JOIN CARS c ON c.id = a.car_id
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             JOIN CUSTOMERS cu ON cu.id = a.customer_id
             JOIN SERVICES s ON s.id = a.service_id
             WHERE a.id = :id'
        );
        $statement->execute(['id' => $id]);
        $appointment = $statement->fetch();

        return $appointment ?: null;
    }

    public function getOwnedById(int $id, int $customerId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.*, s.name AS service_name
             FROM APPOINTMENTS a
             JOIN SERVICES s ON s.id = a.service_id
             WHERE a.id = :id AND a.customer_id = :customer_id'
        );
        $statement->execute(['id' => $id, 'customer_id' => $customerId]);
        $appointment = $statement->fetch();

        return $appointment ?: null;
    }

    public function create(int $customerId, int $carId, int $serviceId, string $date, string $description): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO APPOINTMENTS (customer_id, car_id, service_id, appointment_date, description)
             VALUES (:customer_id, :car_id, :service_id, :appointment_date, :description)'
        );
        $statement->execute([
            'customer_id' => $customerId,
            'car_id' => $carId,
            'service_id' => $serviceId,
            'appointment_date' => $date,
            'description' => $description,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $allowedStatuses = ['pending', 'confirmed', 'checked_in', 'completed', 'cancelled'];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException('Trạng thái lịch hẹn không hợp lệ.');
        }

        $statement = $this->pdo->prepare('UPDATE APPOINTMENTS SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);

        return $statement->rowCount() > 0;
    }
}
