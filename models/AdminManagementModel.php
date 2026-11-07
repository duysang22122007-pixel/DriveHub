<?php

class AdminManagementModel extends BaseModel
{
    public function getAll(string $search = ''): array
    {
        $sql = 'SELECT u.id AS user_id, u.username, u.email, u.status, u.created_at,
                       e.id AS employee_id, e.full_name, e.phone, e.position
                FROM USERS u
                JOIN ROLES r ON r.id = u.role_id AND r.name = \'admin\'
                JOIN EMPLOYEES e ON e.user_id = u.id';
        $parameters = [];

        if ($search !== '') {
            $sql .= ' WHERE e.full_name LIKE :name OR u.email LIKE :email OR e.phone LIKE :phone OR u.username LIKE :username';
            $term = '%' . $search . '%';
            $parameters = [
                'name' => $term,
                'email' => $term,
                'phone' => $term,
                'username' => $term,
            ];
        }

        $sql .= ' ORDER BY e.full_name, u.id';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function getById(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.id AS user_id, u.username, u.email, u.status, u.created_at,
                    e.id AS employee_id, e.full_name, e.phone, e.position
             FROM USERS u
             JOIN ROLES r ON r.id = u.role_id AND r.name = \'admin\'
             JOIN EMPLOYEES e ON e.user_id = u.id
             WHERE u.id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $admin = $statement->fetch();

        return $admin ?: null;
    }

    public function create(
        string $username,
        string $email,
        string $password,
        string $fullName,
        string $phone,
        string $position
    ): int {
        $this->pdo->beginTransaction();

        try {
            $roleStatement = $this->pdo->prepare('SELECT id FROM ROLES WHERE name = \'admin\'');
            $roleStatement->execute();
            $roleId = $roleStatement->fetchColumn();

            if ($roleId === false) {
                throw new RuntimeException('Không tìm thấy vai trò quản trị trong cơ sở dữ liệu.');
            }

            $userStatement = $this->pdo->prepare(
                'INSERT INTO USERS (role_id, username, email, password_hash)
                 VALUES (:role_id, :username, :email, :password_hash)'
            );
            $userStatement->execute([
                'role_id' => $roleId,
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = (int) $this->pdo->lastInsertId();

            $employeeStatement = $this->pdo->prepare(
                'INSERT INTO EMPLOYEES (user_id, full_name, phone, position)
                 VALUES (:user_id, :full_name, :phone, :position)'
            );
            $employeeStatement->execute([
                'user_id' => $userId,
                'full_name' => $fullName,
                'phone' => $phone,
                'position' => $position,
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

    public function update(
        int $userId,
        string $username,
        string $email,
        string $fullName,
        string $phone,
        string $position,
        int $status,
        string $password,
        int $actingUserId
    ): void {
        $this->pdo->beginTransaction();

        try {
            $accountStatement = $this->pdo->prepare(
                'SELECT u.id, u.status
                 FROM USERS u
                 JOIN ROLES r ON r.id = u.role_id AND r.name = \'admin\'
                 WHERE u.id = :user_id
                 FOR UPDATE'
            );
            $accountStatement->execute(['user_id' => $userId]);
            $account = $accountStatement->fetch();

            if (!$account) {
                throw new DomainException('Không tìm thấy tài khoản quản trị.');
            }

            if ($userId === $actingUserId && $status === 0) {
                throw new DomainException('Bạn không thể khóa tài khoản quản trị đang sử dụng.');
            }

            if ((int) $account['status'] === 1 && $status === 0) {
                $adminsStatement = $this->pdo->query(
                    'SELECT u.id, u.status
                     FROM USERS u
                     JOIN ROLES r ON r.id = u.role_id AND r.name = \'admin\'
                     FOR UPDATE'
                );
                $activeAdmins = array_filter(
                    $adminsStatement->fetchAll(),
                    static fn (array $admin): bool => (int) $admin['status'] === 1
                );

                if (count($activeAdmins) <= 1) {
                    throw new DomainException('Không thể khóa quản trị viên đang hoạt động cuối cùng.');
                }
            }

            $userSql = 'UPDATE USERS
                        SET username = :username, email = :email, status = :status';
            $parameters = [
                'username' => $username,
                'email' => $email,
                'status' => $status,
                'user_id' => $userId,
            ];

            if ($password !== '') {
                $userSql .= ', password_hash = :password_hash';
                $parameters['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $userSql .= ' WHERE id = :user_id';
            $userStatement = $this->pdo->prepare($userSql);
            $userStatement->execute($parameters);

            $employeeStatement = $this->pdo->prepare(
                'UPDATE EMPLOYEES
                 SET full_name = :full_name, phone = :phone, position = :position
                 WHERE user_id = :user_id'
            );
            $employeeStatement->execute([
                'full_name' => $fullName,
                'phone' => $phone,
                'position' => $position,
                'user_id' => $userId,
            ]);

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
