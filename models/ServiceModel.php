<?php

class ServiceModel extends BaseModel
{
    public function getByCode(string $code): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM SERVICES WHERE service_code = :code AND status = 1');
        $statement->execute(['code' => $code]);
        $service = $statement->fetch();

        return $service ?: null;
    }

    public function getById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM SERVICES WHERE id = :id AND status = 1');
        $statement->execute(['id' => $id]);
        $service = $statement->fetch();

        return $service ?: null;
    }

    public function getActiveServices(): array
    {
        return $this->pdo->query(
            'SELECT id, name, price
             FROM SERVICES
             WHERE status = 1
             ORDER BY name'
        )->fetchAll();
    }
}
