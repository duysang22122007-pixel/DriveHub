<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

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

    /**
     * @return array{id: int, full_name: string, email: string, phone: string, role: string, status: string}|null
     */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, full_name, email, phone, role, status FROM users WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function updateProfile(int $id, string $fullName, string $email, string $phone): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET full_name = :full_name, email = :email, phone = :phone WHERE id = :id'
        );

        return $statement->execute([
            'full_name' => $fullName,
            'email'     => $email,
            'phone'     => $phone,
            'id'        => $id,
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
            $statement->execute(['password' => $passwordHash, 'id' => $id]);
            $this->deleteUserResetTokens($id);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function getPasswordHash(int $id): ?string
    {
        $statement = $this->pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $result = $statement->fetchColumn();

        return $result === false ? null : (string) $result;
    }

    public function createPasswordResetToken(int $userId, string $token): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->deleteUserResetTokens($userId);
            $statement = $this->pdo->prepare(
                'INSERT INTO password_resets (user_id, token, expires_at)'
                . ' VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
            );
            $statement->execute([
                'user_id' => $userId,
                'token' => hash('sha256', $token),
            ]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function findValidResetToken(string $token): ?int
    {
        $statement = $this->pdo->prepare(
            'SELECT user_id FROM password_resets'
            . ' WHERE token IN (:hashed_token, :raw_token)'
            . ' AND expires_at > NOW() AND used_at IS NULL LIMIT 1'
        );
        $statement->execute([
            'hashed_token' => hash('sha256', $token),
            'raw_token' => $token,
        ]);
        $result = $statement->fetchColumn();

        return $result === false ? null : (int) $result;
    }

    public function resetPasswordWithToken(string $token, string $passwordHash): bool
    {
        $tokenHash = hash('sha256', $token);
        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                'SELECT user_id, token FROM password_resets'
                . ' WHERE token IN (:hashed_token, :raw_token)'
                . ' AND expires_at > NOW() AND used_at IS NULL'
                . ' LIMIT 1 FOR UPDATE'
            );
            $statement->execute([
                'hashed_token' => $tokenHash,
                'raw_token' => $token,
            ]);
            $reset = $statement->fetch(PDO::FETCH_ASSOC);

            if ($reset === false) {
                $this->pdo->rollBack();

                return false;
            }

            $storedToken = (string) $reset['token'];
            $statement = $this->pdo->prepare(
                'UPDATE password_resets SET used_at = NOW()'
                . ' WHERE token = :token AND expires_at > NOW() AND used_at IS NULL'
            );
            $statement->execute(['token' => $storedToken]);

            if ($statement->rowCount() !== 1) {
                $this->pdo->rollBack();

                return false;
            }

            $statement = $this->pdo->prepare(
                'UPDATE users SET password = :password WHERE id = :id'
            );
            $statement->execute(['password' => $passwordHash, 'id' => $reset['user_id']]);

            if ($statement->rowCount() !== 1) {
                throw new \RuntimeException('Password reset token has no matching user.');
            }

            $statement = $this->pdo->prepare(
                'DELETE FROM password_resets WHERE user_id = :user_id AND token <> :token AND used_at IS NULL'
            );
            $statement->execute(['user_id' => $reset['user_id'], 'token' => $storedToken]);
            $this->pdo->commit();

            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function deleteUserResetTokens(int $userId): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM password_resets WHERE user_id = :id AND used_at IS NULL'
        );
        $statement->execute(['id' => $userId]);
    }
}