<?php

class AuthController
{
    public function index()
    {
        if (current_customer() !== null || current_admin() !== null) {
            $this->redirectForRole(current_admin() !== null ? 'admin' : 'customer');
        }

        $requestedMode = $_SERVER['REQUEST_METHOD'] === 'POST'
            ? (string) ($_POST['mode'] ?? '')
            : (string) ($_GET['mode'] ?? 'customer-login');
        $mode = $this->normalizeMode($requestedMode);

        if ($mode === 'admin-login') {
            $this->redirectPath('admin/login');
        }

        if ($mode !== 'register') {
            $this->redirectPath('login');
        }

        $errors = [];
        $old = ['login' => '', 'username' => '', 'email' => '', 'full_name' => '', 'phone' => ''];
        $csrfToken = csrf_token();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->normalizeMode((string) ($_POST['mode'] ?? '')) !== 'register') {
                http_response_code(400);
                exit('Yêu cầu đăng ký không hợp lệ.');
            }

            foreach (array_keys($old) as $key) {
                $old[$key] = trim((string) ($_POST[$key] ?? ''));
            }

            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors['form'] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } else {
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
            }
        }

        $title = 'Đăng ký- DriveHub';
        $view = 'auth/form';
        require_once PATH_VIEW_MAIN;
    }

    public function customerLogin()
    {
        $this->loginAs('customer');
    }

    public function adminLogin()
    {
        $this->loginAs('admin');
    }

    private function loginAs(string $expectedRole): void
    {
        if (current_admin() !== null) {
            $this->redirectForRole('admin');
        }

        if (current_customer() !== null) {
            $this->redirectForRole('customer');
        }

        $errors = [];
        $old = ['login' => ''];
        $csrfToken = csrf_token();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['login'] = trim((string) ($_POST['login'] ?? ''));

            if (!verify_csrf_token((string) ($_POST['csrf_token'] ?? ''))) {
                $errors['form'] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            } else {
                $password = (string) ($_POST['password'] ?? '');
                $user = (new AuthModel())->findByLogin($old['login']);

                if (
                    !$user
                    || (int) $user['status'] !== 1
                    || !password_verify($password, $user['password_hash'])
                ) {
                    $errors['form'] = 'Thông tin đăng nhập không hợp lệ hoặc tài khoản đã bị khóa.';
                } elseif ($user['role'] !== $expectedRole) {
                    $errors['form'] = $expectedRole === 'admin'
                        ? 'Tài khoản này không có quyền quản trị.'
                        : 'Vui lòng sử dụng cổng đăng nhập quản trị viên.';
                } elseif (!in_array($user['role'], ['customer', 'admin'], true)
                    || ($user['role'] === 'customer' && $user['customer_id'] === null)
                    || ($user['role'] === 'admin' && $user['employee_id'] === null)
                ) {
                    $errors['form'] = 'Hồ sơ tài khoản chưa được liên kết hợp lệ.';
                } else {
                    $this->signIn($user);
                    $this->redirectForRole($user['role']);
                }
            }
        }

        $loginRole = $expectedRole;
        $title = $expectedRole === 'admin' ? 'Đăng nhập quản trị - DriveHub' : 'Đăng nhập DriveHub';
        $view = 'auth/customer_login';
        require_once PATH_VIEW_MAIN;
    }

    private function normalizeMode(string $mode): string
    {
        if ($mode === 'login') {
            return 'customer-login';
        }

        return in_array($mode, ['customer-login', 'admin-login', 'register'], true)
            ? $mode
            : 'customer-login';
    }

    public function logout()
    {
        $this->logoutRole('customer');
    }

    public function customerLogout()
    {
        $this->logoutRole('customer');
    }

    public function adminLogout()
    {
        $this->logoutRole('admin');
    }

    private function logoutRole(string $role): void
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)
            || ($_SERVER['REQUEST_METHOD'] === 'POST'
                && !verify_csrf_token((string) ($_POST['csrf_token'] ?? '')))
        ) {
            http_response_code(400);
            exit('Yêu cầu đăng xuất không hợp lệ.');
        }

        unset($_SESSION[$role]);
        if ($role === 'customer') {
            unset($_SESSION['booking_draft'], $_SESSION['booking_csrf']);
        }
        session_regenerate_id(true);
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    private function signIn(?array $user): void
    {
        if ($user === null) {
            throw new RuntimeException('Không thể tải tài khoản vừa tạo.');
        }

        session_regenerate_id(true);

        if ($user['role'] === 'customer') {
            unset($_SESSION['booking_draft'], $_SESSION['booking_csrf']);
            $_SESSION['customer'] = [
                'user_id' => (int) $user['id'],
                'customer_id' => (int) $user['customer_id'],
                'name' => $user['customer_name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
        } else {
            $_SESSION['admin'] = [
                'user_id' => (int) $user['id'],
                'employee_id' => (int) $user['employee_id'],
                'name' => $user['employee_name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
        }
    }

    private function redirectForRole(string $role): void
    {
        $action = $role === 'admin' ? 'admin-dashboard' : 'my-appointments';
        $this->redirect($action);
    }

    private function redirect(string $action): void
    {
        header('Location: ' . BASE_URL . '?action=' . $action);
        exit;
    }

    private function redirectPath(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}
