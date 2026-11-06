<?php

class RepairOrderModel extends BaseModel
{
    public function getAll(): array
    {
        return $this->pdo->query(
            'SELECT ro.*, c.license_plate, c.model AS car_model, b.name AS car_brand,
                    cu.full_name AS customer_name, e.full_name AS employee_name,
                    a.id AS linked_appointment_id
             FROM REPAIR_ORDERS ro
             JOIN CARS c ON c.id = ro.car_id
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             JOIN CUSTOMERS cu ON cu.id = c.customer_id
             JOIN EMPLOYEES e ON e.id = ro.employee_id
             LEFT JOIN APPOINTMENTS a ON a.id = ro.appointment_id
             ORDER BY ro.received_date DESC, ro.id DESC'
        )->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT ro.*, c.license_plate, c.model AS car_model, c.current_mileage,
                    b.name AS car_brand, cu.full_name AS customer_name,
                    a.customer_id, a.status AS appointment_status,
                    e.full_name AS employee_name
             FROM REPAIR_ORDERS ro
             JOIN CARS c ON c.id = ro.car_id
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             JOIN CUSTOMERS cu ON cu.id = c.customer_id
             JOIN EMPLOYEES e ON e.id = ro.employee_id
             LEFT JOIN APPOINTMENTS a ON a.id = ro.appointment_id
             WHERE ro.id = :id'
        );
        $statement->execute(['id' => $id]);
        $order = $statement->fetch();

        if (!$order) {
            return null;
        }

        $detailStatement = $this->pdo->prepare(
            'SELECT rd.*, s.name AS service_name
             FROM REPAIR_DETAILS rd
             JOIN SERVICES s ON s.id = rd.service_id
             WHERE rd.repair_order_id = :repair_order_id
             ORDER BY rd.id'
        );
        $detailStatement->execute(['repair_order_id' => $id]);
        $order['details'] = $detailStatement->fetchAll();

        return $order;
    }

    public function getForCustomer(int $id, int $customerId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT ro.*, c.license_plate, c.model AS car_model, b.name AS car_brand,
                    s.name AS service_name
             FROM REPAIR_ORDERS ro
             JOIN CARS c ON c.id = ro.car_id
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             LEFT JOIN REPAIR_DETAILS rd ON rd.repair_order_id = ro.id
             LEFT JOIN SERVICES s ON s.id = rd.service_id
             WHERE ro.id = :id AND c.customer_id = :customer_id
             GROUP BY ro.id, c.id, b.id, s.id'
        );
        $statement->execute(['id' => $id, 'customer_id' => $customerId]);
        $order = $statement->fetch();

        if (!$order) {
            return null;
        }

        $detailStatement = $this->pdo->prepare(
            'SELECT rd.*, s.name AS service_name
             FROM REPAIR_DETAILS rd
             JOIN SERVICES s ON s.id = rd.service_id
             WHERE rd.repair_order_id = :repair_order_id
             ORDER BY rd.id'
        );
        $detailStatement->execute(['repair_order_id' => $id]);
        $order['details'] = $detailStatement->fetchAll();

        return $order;
    }

    public function createFromAppointment(
        int $appointmentId,
        int $carId,
        int $employeeId,
        string $problemDesc,
        ?int $changedByUserId = null
    ): int
    {
        $this->pdo->beginTransaction();

        try {
            $appointmentStatement = $this->pdo->prepare(
                'SELECT a.*, s.price, c.current_mileage
                 FROM APPOINTMENTS a
                 JOIN SERVICES s ON s.id = a.service_id
                 JOIN CARS c ON c.id = a.car_id
                 WHERE a.id = :id
                 FOR UPDATE'
            );
            $appointmentStatement->execute(['id' => $appointmentId]);
            $appointment = $appointmentStatement->fetch();

            if (!$appointment) {
                throw new DomainException('Không tìm thấy lịch hẹn cần tiếp nhận.');
            }

            if ((int) $appointment['car_id'] !== $carId) {
                throw new DomainException('Xe trong phiếu tiếp nhận không khớp với lịch hẹn.');
            }

            if (!in_array($appointment['status'], ['pending', 'confirmed'], true)) {
                throw new DomainException('Lịch hẹn này không còn ở trạng thái có thể tiếp nhận.');
            }

            $existingOrder = $this->pdo->prepare('SELECT id FROM REPAIR_ORDERS WHERE appointment_id = :appointment_id');
            $existingOrder->execute(['appointment_id' => $appointmentId]);
            if ($existingOrder->fetchColumn() !== false) {
                throw new DomainException('Lịch hẹn này đã được chuyển thành phiếu sửa chữa.');
            }

            $insertStatement = $this->pdo->prepare(
                "INSERT INTO REPAIR_ORDERS (appointment_id, car_id, employee_id, received_mileage, problem_description, status)
                 VALUES (:appointment_id, :car_id, :employee_id, :received_mileage, :problem_description, 'received')"
            );
            $insertStatement->execute([
                'appointment_id' => $appointmentId,
                'car_id' => $carId,
                'employee_id' => $employeeId,
                'received_mileage' => $appointment['current_mileage'] ?? 0,
                'problem_description' => $problemDesc,
            ]);
            $repairOrderId = (int) $this->pdo->lastInsertId();

            $detailStatement = $this->pdo->prepare(
                'INSERT INTO REPAIR_DETAILS (repair_order_id, service_id, quantity, unit_price, note)
                 VALUES (:repair_order_id, :service_id, 1, :unit_price, :note)'
            );
            $detailStatement->execute([
                'repair_order_id' => $repairOrderId,
                'service_id' => $appointment['service_id'],
                'unit_price' => $appointment['price'],
                'note' => $problemDesc,
            ]);

            if (!(new AppointmentModel())->updateStatus($appointmentId, 'checked_in', $changedByUserId)) {
                throw new DomainException('Không thể cập nhật lịch hẹn khi tiếp nhận xe.');
            }

            $this->pdo->commit();
            return $repairOrderId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['in_progress', 'cancelled'], true)) {
            throw new InvalidArgumentException('Trạng thái phiếu sửa chữa không hợp lệ.');
        }

        $allowedCurrent = $status === 'in_progress' ? ['received'] : ['received', 'in_progress'];
        $placeholders = implode(', ', array_fill(0, count($allowedCurrent), '?'));
        $statement = $this->pdo->prepare(
            "UPDATE REPAIR_ORDERS SET status = ? WHERE id = ? AND status IN ($placeholders)"
        );
        $statement->execute(array_merge([$status, $id], $allowedCurrent));

        return $statement->rowCount() > 0;
    }

    public function complete(int $id, int $mileage, string $diagnosis, ?int $changedByUserId = null): void
    {
        $this->pdo->beginTransaction();

        try {
            $orderStatement = $this->pdo->prepare(
                'SELECT ro.*, c.current_mileage
                 FROM REPAIR_ORDERS ro
                 JOIN CARS c ON c.id = ro.car_id
                 WHERE ro.id = :id
                 FOR UPDATE'
            );
            $orderStatement->execute(['id' => $id]);
            $order = $orderStatement->fetch();

            if (!$order) {
                throw new DomainException('Không tìm thấy phiếu sửa chữa.');
            }

            if (in_array($order['status'], ['completed', 'cancelled'], true)) {
                throw new DomainException('Phiếu sửa chữa đã hoàn tất hoặc đã hủy.');
            }

            if ($mileage < max((int) $order['current_mileage'], (int) $order['received_mileage'])) {
                throw new DomainException('Số km hoàn tất không được thấp hơn số km hiện tại hoặc lúc tiếp nhận.');
            }

            $updateOrder = $this->pdo->prepare(
                "UPDATE REPAIR_ORDERS
                 SET status = 'completed', diagnosis = :diagnosis, completed_mileage = :mileage, completed_at = NOW()
                 WHERE id = :id"
            );
            $updateOrder->execute(['diagnosis' => $diagnosis, 'mileage' => $mileage, 'id' => $id]);

            if ($order['appointment_id'] !== null) {
                if (!(new AppointmentModel())->updateStatus((int) $order['appointment_id'], 'completed', $changedByUserId)) {
                    throw new DomainException('Không thể cập nhật lịch hẹn khi hoàn tất sửa chữa.');
                }
            }

            $historyStatement = $this->pdo->prepare(
                'INSERT INTO MAINTENANCE_HISTORY (car_id, repair_order_id, maintenance_date, mileage, description)
                 SELECT ro.car_id, ro.id, NOW(), :mileage,
                        CONCAT_WS(CHAR(10), NULLIF(:diagnosis, \'\'), ro.problem_description,
                            (SELECT GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR \', \')
                             FROM REPAIR_DETAILS rd JOIN SERVICES s ON s.id = rd.service_id
                             WHERE rd.repair_order_id = ro.id))
                 FROM REPAIR_ORDERS ro
                 WHERE ro.id = :repair_order_id'
            );
            $historyStatement->execute([
                'mileage' => $mileage,
                'diagnosis' => trim($diagnosis),
                'repair_order_id' => $id,
            ]);

            $updateCar = $this->pdo->prepare('UPDATE CARS SET current_mileage = :mileage WHERE id = :car_id');
            $updateCar->execute(['mileage' => $mileage, 'car_id' => $order['car_id']]);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
