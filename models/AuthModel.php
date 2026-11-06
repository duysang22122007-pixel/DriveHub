<?php

class AuthModel extends BaseModel
{
    public function findByLogin(string $login): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.id, u.username, u.email, u.password_hash, u.status, r.name AS role,
                    c.id AS customer_id, c.full_name AS customer_name, c.phone AS customer_phone,
                    e.id AS employee_id, e.full_name AS employee_name
             FROM USERS u
             JOIN ROLES r ON r.id = u.role_id
             LEFT JOIN CUSTOMERS c ON c.user_id = u.id
             LEFT JOIN EMPLOYEES e ON e.user_id = u.id
             WHERE u.username = :login OR u.email = :login
             LIMIT 1'
        );
        $statement->execute(['login' => $login]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function registerCustomer(string $username, string $email, string $password, string $fullName, string $phone): int
    {
        $this->pdo->beginTransaction();

        try {
            $roleStatement = $this->pdo->prepare("SELECT id FROM ROLES WHERE name = 'customer'");
            $roleStatement->execute();
            $roleId = $roleStatement->fetchColumn();

            if ($roleId === false) {
                throw new RuntimeException('Không tìm thấy vai trò khách hàng trong cơ sở dữ liệu.');
            }

            $userStatement = $this->pdo->prepare(
                'INSERT INTO USERS (role_id, username, email, password_hash) VALUES (:role_id, :username, :email, :password_hash)'
            );
            $userStatement->execute([
                'role_id' => $roleId,
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = (int) $this->pdo->lastInsertId();

            $customerStatement = $this->pdo->prepare(
                'INSERT INTO CUSTOMERS (user_id, full_name, phone) VALUES (:user_id, :full_name, :phone)'
            );
            $customerStatement->execute([
                'user_id' => $userId,
                'full_name' => $fullName,
                'phone' => $phone,
            ]);

            $this->pdo->commit();
            return $userId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function getCustomerProfile(int $customerId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT c.id, c.full_name, c.phone, u.email
             FROM CUSTOMERS c
             JOIN USERS u ON u.id = c.user_id
             WHERE c.id = :customer_id AND u.status = 1'
        );
        $statement->execute(['customer_id' => $customerId]);
        $profile = $statement->fetch();

        return $profile ?: null;
    }
}
