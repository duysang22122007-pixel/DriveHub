<?php

class AuthController
{
    public function index()
    {
        if (current_user() !== null) {
            $this->redirectForRole(current_user()['role']);
        }

        $mode = (string) ($_GET['mode'] ?? 'login');
        if (!in_array($mode, ['login', 'register'], true)) {
            $mode = 'login';
        }

        $errors = [];
        $old = ['login' => '', 'username' => '', 'email' => '', 'full_name' => '', 'phone' => ''];
        $csrfToken = csrf_token();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mode = (string) ($_POST['mode'] ?? 'login');

            if (!in_array($mode, ['login', 'register'], true)) {
                $mode = 'login';
            }

            foreach (array_keys($old) as $key) {
                $old[$key] = trim((string) ($_POST[$key] ?? ''));
            }

            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors['form'] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } elseif ($mode === 'register') {
                $password = (string) ($_POST['password'] ?? '');
                $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

                if (!preg_match('/^[A-Za-z0-9._-]{3,80}$/', $old['username'])) {
                    $errors['username'] = 'Tên đăng nhập cần từ 3 đến 80 ký tự (chữ, số, dấu chấm, gạch dưới hoặc gạch nối).';
                }

                if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
                    $errors['email'] = 'Email chưa đúng định dạng.';
                }

                if (mb_strlen($old['full_name']) < 2 || mb_strlen($old['full_name']) > 100) {
                    $errors['full_name'] = 'Họ tên cần từ 2 đến 100 ký tự.';
                }

                if (!preg_match('/^(0\d{9}|\+84\d{9})$/', preg_replace('/[\s.\-()]+/u', '', $old['phone']))) {
                    $errors['phone'] = 'Số điện thoại chưa đúng định dạng Việt Nam.';
                }

                if (strlen($password) < 10) {
                    $errors['password'] = 'Mật khẩu cần có ít nhất 10 ký tự.';
                } elseif ($password !== $passwordConfirmation) {
                    $errors['password_confirmation'] = 'Mật khẩu xác nhận không khớp.';
                }

                if ($errors === []) {
                    try {
                        $authModel = new AuthModel();
                        $authModel->registerCustomer(
                            $old['username'],
                            strtolower($old['email']),
                            $password,
                            $old['full_name'],
                            preg_replace('/[\s.\-()]+/u', '', $old['phone'])
                        );
                        $this->signIn($authModel->findByLogin($old['username']));
                        $this->redirectForRole('customer');
                    } catch (PDOException $exception) {
                        if ($exception->getCode() !== '23000') {
                            throw $exception;
                        }

                        $errors['form'] = 'Tên đăng nhập hoặc email này đã được sử dụng.';
                    }
                }
            } else {
                $password = (string) ($_POST['password'] ?? '');
                $user = (new AuthModel())->findByLogin($old['login']);

                if (
                    !$user
                    || (int) $user['status'] !== 1
                    || !password_verify($password, $user['password_hash'])
                    || !in_array($user['role'], ['customer', 'admin'], true)
                    || ($user['role'] === 'customer' && $user['customer_id'] === null)
                    || ($user['role'] === 'admin' && $user['employee_id'] === null)
                ) {
                    $errors['form'] = 'Thông tin đăng nhập không hợp lệ hoặc tài khoản đã bị khóa.';
                } else {
                    $this->signIn($user);
                    $this->redirectForRole($user['role']);
                }
            }
        }

        $title = $mode === 'register' ? 'Đăng ký khách hàng - DriveHub' : 'Đăng nhập - DriveHub';
        $view = 'auth/form';
        require_once PATH_VIEW_MAIN;
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
            http_response_code(400);
            exit('Yêu cầu đăng xuất không hợp lệ.');
        }

        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: ' . BASE_URL);
        exit;
    }

    private function signIn(?array $user): void
    {
        if ($user === null) {
            throw new RuntimeException('Không thể tải tài khoản vừa tạo.');
        }

        session_regenerate_id(true);
        unset($_SESSION['booking_draft'], $_SESSION['booking_csrf']);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'customer_id' => $user['customer_id'] !== null ? (int) $user['customer_id'] : null,
            'employee_id' => $user['employee_id'] !== null ? (int) $user['employee_id'] : null,
        ];
    }

    private function redirectForRole(string $role): void
    {
        $action = $role === 'admin' ? 'admin-appointments' : 'my-appointments';
        header('Location: ' . BASE_URL . '?action=' . $action);
        exit;
    }
}
