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

    public function getTimelineByCustomerId(int $customerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.*, c.license_plate, c.model AS car_model, b.name AS car_brand,
                    s.name AS service_name, ro.id AS repair_order_id, ro.status AS repair_status
             FROM APPOINTMENTS a
             JOIN CARS c ON c.id = a.car_id
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             JOIN SERVICES s ON s.id = a.service_id
             LEFT JOIN REPAIR_ORDERS ro ON ro.appointment_id = a.id
             WHERE a.customer_id = :customer_id
             ORDER BY a.created_at DESC, a.id DESC'
        );
        $statement->execute(['customer_id' => $customerId]);
        $appointments = $statement->fetchAll();

        if ($appointments === []) {
            return [];
        }

        $appointmentIds = array_column($appointments, 'id');
        $placeholders = implode(', ', array_fill(0, count($appointmentIds), '?'));
        $historyStatement = $this->pdo->prepare(
            "SELECT appointment_id, status, changed_at, changed_by_user_id, note
             FROM APPOINTMENT_STATUS_HISTORY
             WHERE appointment_id IN ($placeholders)
             ORDER BY changed_at, id"
        );
        $historyStatement->execute($appointmentIds);
        $historyByAppointment = [];

        foreach ($historyStatement->fetchAll() as $event) {
            $historyByAppointment[$event['appointment_id']][] = $event;
        }

        foreach ($appointments as &$appointment) {
            $appointment['status_history'] = $historyByAppointment[$appointment['id']] ?? [];
        }
        unset($appointment);

        return $appointments;
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

    public function create(
        int $customerId,
        int $carId,
        int $serviceId,
        string $date,
        string $description,
        ?int $changedByUserId = null
    ): int
    {
        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
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
            $appointmentId = (int) $this->pdo->lastInsertId();

            $this->recordStatus($appointmentId, 'pending', $changedByUserId, 'Khách hàng gửi yêu cầu đặt lịch.');

            if ($ownsTransaction) {
                $this->pdo->commit();
            }

            return $appointmentId;
        } catch (Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function updateStatus(int $id, string $status, ?int $changedByUserId = null): bool
    {
        $allowedStatuses = ['pending', 'confirmed', 'checked_in', 'completed', 'cancelled'];

        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException('Trạng thái lịch hẹn không hợp lệ.');
        }

        $allowedTransitions = [
            'pending' => ['confirmed', 'checked_in', 'cancelled'],
            'confirmed' => ['checked_in', 'completed', 'cancelled'],
            'checked_in' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];
        $ownsTransaction = !$this->pdo->inTransaction();

        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            $currentStatement = $this->pdo->prepare('SELECT status FROM APPOINTMENTS WHERE id = :id FOR UPDATE');
            $currentStatement->execute(['id' => $id]);
            $currentStatus = $currentStatement->fetchColumn();

            if ($currentStatus === false || $currentStatus === $status) {
                if ($ownsTransaction) {
                    $this->pdo->commit();
                }

                return false;
            }

            if (!in_array($status, $allowedTransitions[$currentStatus] ?? [], true)) {
                throw new DomainException('Không thể chuyển trạng thái lịch hẹn theo yêu cầu.');
            }

            $statement = $this->pdo->prepare('UPDATE APPOINTMENTS SET status = :status WHERE id = :id');
            $statement->execute(['status' => $status, 'id' => $id]);
            $this->recordStatus($id, $status, $changedByUserId);

            if ($ownsTransaction) {
                $this->pdo->commit();
            }

            return true;
        } catch (Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function recordStatus(int $appointmentId, string $status, ?int $changedByUserId, ?string $note = null): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO APPOINTMENT_STATUS_HISTORY (appointment_id, status, changed_by_user_id, note)
             VALUES (:appointment_id, :status, :changed_by_user_id, :note)'
        );
        $statement->execute([
            'appointment_id' => $appointmentId,
            'status' => $status,
            'changed_by_user_id' => $changedByUserId,
            'note' => $note,
        ]);
    }
}
