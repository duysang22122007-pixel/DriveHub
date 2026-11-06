<?php

class CarModel extends BaseModel
{
    public function getByCustomerId(int $customerId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.*, b.name AS brand
             FROM CARS c
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             WHERE c.customer_id = :customer_id
             ORDER BY c.license_plate'
        );
        $statement->execute(['customer_id' => $customerId]);

        return $statement->fetchAll();
    }

    public function getOwnedCar(int $carId, int $customerId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.*, b.name AS brand
             FROM CARS c
             JOIN CAR_BRANDS b ON b.id = c.brand_id
             WHERE c.id = :car_id AND c.customer_id = :customer_id'
        );
        $statement->execute(['car_id' => $carId, 'customer_id' => $customerId]);
        $car = $statement->fetch();

        return $car ?: null;
    }

    public function getOrCreateForCustomer(int $customerId, array $data): int
    {
        $brandStatement = $this->pdo->prepare(
            'INSERT INTO CAR_BRANDS (name) VALUES (:name)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
        );
        $brandStatement->execute(['name' => $data['car_brand']]);
        $brandId = (int) $this->pdo->lastInsertId();

        $findStatement = $this->pdo->prepare('SELECT id, customer_id FROM CARS WHERE license_plate = :license_plate');
        $findStatement->execute(['license_plate' => $data['license_plate']]);
        $existing = $findStatement->fetch();

        if ($existing) {
            if ((int) $existing['customer_id'] !== $customerId) {
                throw new DomainException('Biển số xe này đã được đăng ký bởi một tài khoản khác.');
            }

            $updateStatement = $this->pdo->prepare(
                'UPDATE CARS SET brand_id = :brand_id, model = :model, manufacture_year = :manufacture_year WHERE id = :id'
            );
            $updateStatement->execute([
                'brand_id' => $brandId,
                'model' => $data['car_model'],
                'manufacture_year' => $data['manufacture_year'] !== '' ? $data['manufacture_year'] : null,
                'id' => $existing['id'],
            ]);

            return (int) $existing['id'];
        }

        $insertStatement = $this->pdo->prepare(
            'INSERT INTO CARS (customer_id, brand_id, license_plate, model, manufacture_year)
             VALUES (:customer_id, :brand_id, :license_plate, :model, :manufacture_year)'
        );
        $insertStatement->execute([
            'customer_id' => $customerId,
            'brand_id' => $brandId,
            'license_plate' => $data['license_plate'],
            'model' => $data['car_model'],
            'manufacture_year' => $data['manufacture_year'] !== '' ? $data['manufacture_year'] : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
