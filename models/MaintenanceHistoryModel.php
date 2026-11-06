<?php

class MaintenanceHistoryModel extends BaseModel
{
    public function addHistory(int $carId, int $repairOrderId, string $date, int $mileage, string $description): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO MAINTENANCE_HISTORY (car_id, repair_order_id, maintenance_date, mileage, description)
             VALUES (:car_id, :repair_order_id, :maintenance_date, :mileage, :description)'
        );
        $statement->execute([
            'car_id' => $carId,
            'repair_order_id' => $repairOrderId,
            'maintenance_date' => $date,
            'mileage' => $mileage,
            'description' => $description,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getHistoryByCarId(int $carId): array
    {
        return $this->getHistory('c.id = :id', $carId);
    }

    public function getHistoryByCustomerId(int $customerId): array
    {
        return $this->getHistory('c.customer_id = :id', $customerId);
    }

    private function getHistory(string $condition, int $id): array
    {
        $statement = $this->pdo->prepare(
            "SELECT h.*, c.license_plate, c.model AS car_model, b.name AS car_brand,
                    ro.diagnosis, GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS service_names,
                    MIN(rd.service_id) AS service_id
             FROM MAINTENANCE_HISTORY h
             JOIN CARS c ON c.id = h.car_id
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             JOIN REPAIR_ORDERS ro ON ro.id = h.repair_order_id
             LEFT JOIN REPAIR_DETAILS rd ON rd.repair_order_id = ro.id
             LEFT JOIN SERVICES s ON s.id = rd.service_id
             WHERE $condition
             GROUP BY h.id, c.id, b.id, ro.id
             ORDER BY h.maintenance_date DESC, h.id DESC"
        );
        $statement->execute(['id' => $id]);

        return $statement->fetchAll();
    }
}
