<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class User
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{id: int, full_name: string, email: string, phone: string, password: string, role: string, status: string}|null
     */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, full_name, email, phone, password, role, status FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    /**
     * @return array{id: int, full_name: string, email: string, role: string, status: string}|null
     */
    public function findPublicById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, full_name, email, role, status FROM users WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function createCustomer(string $fullName, string $email, string $phone, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO users (full_name, email, phone, password, role, status)
             VALUES (:full_name, :email, :phone, :password, 'customer', 'active')"
        );
        $statement->execute([
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password' => $passwordHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function createAdmin(string $fullName, string $email, string $phone, string $passwordHash): int
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO users (full_name, email, phone, password, role, status)
             VALUES (:full_name, :email, :phone, :password, 'admin', 'active')"
        );
        $statement->execute([
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password' => $passwordHash,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function hasAdmin(): bool
    {
        $statement = $this->pdo->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1");

        return $statement->fetchColumn() !== false;
    }

    public function updatePasswordHash(int $id, string $passwordHash): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
        $statement->execute(['password' => $passwordHash, 'id' => $id]);
    }
}
