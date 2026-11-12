<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Vehicle
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forCustomer(int $customerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT v.id, v.brand_id, b.name AS brand_name, v.model, v.license_plate,
                    v.manufacture_year, v.color, v.current_mileage
             FROM vehicles AS v
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             WHERE v.user_id = :user_id
             ORDER BY v.created_at DESC, v.id DESC'
        );
        $statement->execute(['user_id' => $customerId]);

        return $statement->fetchAll();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function activeBrands(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, name FROM vehicle_brands WHERE status = 1 ORDER BY name'
        );

        return $statement->fetchAll();
    }

    public function hasActiveBrand(int $brandId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1 FROM vehicle_brands WHERE id = :id AND status = 1'
        );
        $statement->execute(['id' => $brandId]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForCustomer(int $vehicleId, int $customerId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, brand_id, model, license_plate, manufacture_year, color, current_mileage
             FROM vehicles WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute(['id' => $vehicleId, 'user_id' => $customerId]);
        $vehicle = $statement->fetch();

        return $vehicle === false ? null : $vehicle;
    }

    /**
     * @param array{brand_id: int, model_id: ?int, model: string, license_plate: string, manufacture_year: ?int, color: ?string, current_mileage: int} $data
     */
    public function create(int $customerId, array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO vehicles
                (user_id, brand_id, vehicle_model_id, model, license_plate, manufacture_year, color, current_mileage)
             VALUES
                (:user_id, :brand_id, :vehicle_model_id, :model, :license_plate, :manufacture_year, :color, :current_mileage)'
        );
        $statement->execute([
            'user_id' => $customerId,
            'brand_id' => $data['brand_id'],
            'vehicle_model_id' => $data['model_id'],
            'model' => $data['model'],
            'license_plate' => $data['license_plate'],
            'manufacture_year' => $data['manufacture_year'],
            'color' => $data['color'],
            'current_mileage' => $data['current_mileage'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array{brand_id: int, model_id: ?int, model: string, license_plate: string, manufacture_year: ?int, color: ?string, current_mileage: int} $data
     */
    public function updateForCustomer(int $vehicleId, int $customerId, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE vehicles
             SET brand_id = :brand_id, vehicle_model_id = NULL, model = :model, license_plate = :license_plate,
                 manufacture_year = :manufacture_year, color = :color, current_mileage = :current_mileage
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            'brand_id' => $data['brand_id'],
            'model' => $data['model'],
            'license_plate' => $data['license_plate'],
            'manufacture_year' => $data['manufacture_year'],
            'color' => $data['color'],
            'current_mileage' => $data['current_mileage'],
            'id' => $vehicleId,
            'user_id' => $customerId,
        ]);

        return $statement->rowCount() > 0;
    }

    /**
     * @return bool False when appointments prevent deleting the vehicle.
     */
    public function deleteUnusedForCustomer(int $vehicleId, int $customerId): ?bool
    {
        $this->pdo->beginTransaction();

        try {
            $vehicle = $this->pdo->prepare(
                'SELECT id FROM vehicles WHERE id = :id AND user_id = :user_id FOR UPDATE'
            );
            $vehicle->execute(['id' => $vehicleId, 'user_id' => $customerId]);
            if ($vehicle->fetchColumn() === false) {
                $this->pdo->commit();

                return null;
            }

            $appointments = $this->pdo->prepare(
                'SELECT 1 FROM appointments WHERE vehicle_id = :vehicle_id LIMIT 1'
            );
            $appointments->execute(['vehicle_id' => $vehicleId]);
            if ($appointments->fetchColumn() !== false) {
                $this->pdo->commit();

                return false;
            }

            $delete = $this->pdo->prepare(
                'DELETE FROM vehicles WHERE id = :id AND user_id = :user_id'
            );
            $delete->execute(['id' => $vehicleId, 'user_id' => $customerId]);
            $this->pdo->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allForAdmin(): array
    {
        $statement = $this->pdo->query(
            'SELECT v.id, v.model, v.license_plate, v.manufacture_year, v.current_mileage,
                    b.name AS brand_name, u.full_name AS owner_name, u.email AS owner_email
             FROM vehicles AS v
             INNER JOIN vehicle_brands AS b ON b.id = v.brand_id
             INNER JOIN users AS u ON u.id = v.user_id
             ORDER BY v.created_at DESC, v.id DESC'
        );

        return $statement->fetchAll();
    }
}
