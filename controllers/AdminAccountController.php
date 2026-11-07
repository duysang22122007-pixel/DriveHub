<?php

class AdminAccountController
{
    public function index(): void
    {
        require_role('admin');
        $search = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);
        $admins = (new AdminManagementModel())->getAll($search);
        $notice = (string) ($_GET['notice'] ?? '');
        $flash = $notice === 'created'
            ? 'Đã tạo tài khoản quản trị.'
            : ($notice === 'updated' ? 'Đã cập nhật tài khoản quản trị.' : null);
        $title = 'Quản lý tài khoản quản trị - DriveHub';
        $view = 'admin/accounts';
        require_once PATH_VIEW_MAIN;
    }

    public function create(): void
    {
        require_role('admin');
        $errors = [];
        $values = $this->emptyValues();
        $csrfToken = csrf_token();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $values = $this->readValues($_POST);

            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors[] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } else {
                [$values, $errors] = $this->validateValues($_POST, true);

                if ($errors === []) {
                    try {
                        (new AdminManagementModel())->create(
                            $values['username'],
                            $values['email'],
                            (string) $_POST['password'],
                            $values['full_name'],
                            $values['phone'],
                            $values['position']
                        );
                        header('Location: ' . BASE_URL . '?action=admin-users&notice=created');
                        exit;
                    } catch (PDOException $exception) {
                        if ($exception->getCode() !== '23000') {
                            throw $exception;
                        }
                        $errors[] = 'Tên đăng nhập hoặc email này đã được sử dụng.';
                    }
                }
            }
        }

        $title = 'Tạo tài khoản quản trị - DriveHub';
        $view = 'admin/account-form';
        require_once PATH_VIEW_MAIN;
    }

    public function show(): void
    {
        $admin = require_role('admin');
        $userId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $model = new AdminManagementModel();
        $account = $userId ? $model->getById((int) $userId) : null;

        if (!$account) {
            http_response_code(404);
            exit('Không tìm thấy tài khoản quản trị.');
        }

        $errors = [];
        $values = $account;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors[] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } else {
                [$values, $errors] = $this->validateValues($_POST, false);

                if ($errors === []) {
                    try {
                        $model->update(
                            (int) $account['user_id'],
                            $values['username'],
                            $values['email'],
                            $values['full_name'],
                            $values['phone'],
                            $values['position'],
                            (int) $values['status'],
                            (string) ($_POST['password'] ?? ''),
                            (int) $admin['user_id']
                        );
                        header('Location: ' . BASE_URL . '?action=admin-users&notice=updated');
                        exit;
                    } catch (PDOException $exception) {
                        if ($exception->getCode() !== '23000') {
                            throw $exception;
                        }
                        $errors[] = 'Tên đăng nhập hoặc email này đã được sử dụng.';
                    } catch (DomainException $exception) {
                        $errors[] = $exception->getMessage();
                    }
                }
            }
        }

        $csrfToken = csrf_token();
        $title = 'Chi tiết tài khoản quản trị - DriveHub';
        $view = 'admin/account-detail';
        require_once PATH_VIEW_MAIN;
    }

    private function emptyValues(): array
    {
        return [
            'username' => '',
            'email' => '',
            'full_name' => '',
            'phone' => '',
            'position' => 'Quản trị gara',
            'status' => '1',
        ];
    }

    private function readValues(array $input): array
    {
        return [
            'username' => trim((string) ($input['username'] ?? '')),
            'email' => strtolower(trim((string) ($input['email'] ?? ''))),
            'full_name' => trim((string) ($input['full_name'] ?? '')),
            'phone' => preg_replace('/[\s.\-()]+/u', '', trim((string) ($input['phone'] ?? ''))),
            'position' => trim((string) ($input['position'] ?? '')),
            'status' => (string) ($input['status'] ?? '1'),
        ];
    }

    private function validateValues(array $input, bool $creating): array
    {
        $values = $this->readValues($input);
        $errors = [];
        $password = (string) ($input['password'] ?? '');
        $passwordConfirmation = (string) ($input['password_confirmation'] ?? '');

        if (!preg_match('/^[A-Za-z0-9._-]{3,80}$/', $values['username'])) {
            $errors[] = 'Tên đăng nhập cần từ 3 đến 80 ký tự (chữ, số, dấu chấm, gạch dưới hoặc gạch nối).';
        }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['email']) > 150) {
            $errors[] = 'Email chưa đúng định dạng.';
        }
        if (mb_strlen($values['full_name']) < 2 || mb_strlen($values['full_name']) > 100) {
            $errors[] = 'Họ tên cần từ 2 đến 100 ký tự.';
        }
        if (!preg_match('/^(0\d{9}|\+84\d{9})$/', $values['phone'])) {
            $errors[] = 'Số điện thoại chưa đúng định dạng Việt Nam.';
        }
        if ($values['position'] === '' || mb_strlen($values['position']) > 80) {
            $errors[] = 'Chức vụ cần từ 1 đến 80 ký tự.';
        }
        if (!in_array($values['status'], ['0', '1'], true)) {
            $errors[] = 'Trạng thái tài khoản không hợp lệ.';
        }
        if (($creating || $password !== '') && strlen($password) < 12) {
            $errors[] = 'Mật khẩu quản trị cần có ít nhất 12 ký tự.';
        }
        if (($creating || $password !== '') && $password !== $passwordConfirmation) {
            $errors[] = 'Mật khẩu xác nhận không khớp.';
        }

        return [$values, $errors];
    }
}
