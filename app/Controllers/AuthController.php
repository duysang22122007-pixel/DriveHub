<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\AdminDashboard;
use App\Models\User;
use PDOException;

final class AuthController extends Controller
{
    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function showRegister(): Response
    {
        return $this->view('auth/register', [
            'errors' => [],
            'old' => [],
        ]);
    }

    public function register(Request $request): Response
    {
        $input = $request->body();
        $fullName = is_string($input['full_name'] ?? null) ? trim($input['full_name']) : '';
        $email = is_string($input['email'] ?? null) ? strtolower(trim($input['email'])) : '';
        $phone = is_string($input['phone'] ?? null) ? trim($input['phone']) : '';
        $password = $input['password'] ?? null;
        $passwordConfirmation = $input['password_confirmation'] ?? null;
        $errors = [];

        if (!Validator::stringLength($fullName, 2, 100)) {
            $errors['full_name'] = 'Họ và tên phải có từ 2 đến 100 ký tự.';
        }
        if (!Validator::email($email) || strlen($email) > 150) {
            $errors['email'] = 'Vui lòng nhập email hợp lệ (tối đa 150 ký tự).';
        }
        if (!preg_match('/\A\+?[0-9][0-9\s().-]{6,19}\z/', $phone)) {
            $errors['phone'] = 'Vui lòng nhập số điện thoại hợp lệ (tối đa 20 ký tự).';
        }
        if (!is_string($password) || strlen($password) < 8 || strlen($password) > 72) {
            $errors['password'] = 'Mật khẩu phải có từ 8 đến 72 byte.';
        }
        if (!is_string($passwordConfirmation) || !is_string($password) || !hash_equals($password, $passwordConfirmation)) {
            $errors['password_confirmation'] = 'Mật khẩu xác nhận không khớp.';
        }

        if ($errors !== []) {
            return $this->view('auth/register', [
                'errors' => $errors,
                'old' => ['full_name' => $fullName, 'email' => $email, 'phone' => $phone],
            ], 422);
        }

        try {
            (new User(Database::connection($this->databaseConfig)))->createCustomer(
                $fullName,
                $email,
                $phone,
                password_hash($password, PASSWORD_DEFAULT)
            );
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return $this->view('auth/register', [
                    'errors' => ['email' => 'Email đã được sử dụng.'],
                    'old' => ['full_name' => $fullName, 'email' => $email, 'phone' => $phone],
                ], 422);
            }

            throw $exception;
        }

        flash('success', 'Tạo tài khoản thành công. Vui lòng đăng nhập.');

        return Response::redirect(url('login'));
    }

    public function showLogin(): Response
    {
        return $this->view('auth/login', [
            'errors' => [],
            'old' => [],
            'success' => flash('success'),
        ]);
    }

    public function login(Request $request): Response
    {
        $emailInput = $request->input('email');
        $email = is_string($emailInput) ? strtolower(trim($emailInput)) : '';
        $password = $request->input('password');
        $errors = [];

        if (!Validator::email($email) || strlen($email) > 150) {
            $errors['email'] = 'Vui lòng nhập email hợp lệ.';
        }
        if (!is_string($password) || $password === '' || strlen($password) > 255) {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        }

        if ($errors !== []) {
            return $this->view('auth/login', [
                'errors' => $errors,
                'old' => ['email' => $email],
                'success' => null,
            ], 422);
        }

        $users = new User(Database::connection($this->databaseConfig));
        $user = $users->findByEmail($email);

        if ($user === null || $user['status'] !== 'active' || !password_verify($password, $user['password'])) {
            return $this->view('auth/login', [
                'errors' => ['credentials' => 'Email hoặc mật khẩu không chính xác.'],
                'old' => ['email' => $email],
                'success' => null,
            ], 422);
        }

        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $users->updatePasswordHash((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        session_regenerate_id(true);
        unset($_SESSION['_csrf_token']);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];

        return Response::redirect(url('dashboard'));
    }

    public function logout(): Response
    {
        unset(
            $_SESSION['user_id'],
            $_SESSION['user_role'],
            $_SESSION['user_name'],
            $_SESSION['_csrf_token'],
            $_SESSION['_flash']
        );
        session_regenerate_id(true);

        flash('success', 'Bạn đã đăng xuất.');

        return Response::redirect(url('login'));
    }

    public function dashboard(): Response
    {
        $role = $_SESSION['user_role'] ?? null;

        return Response::redirect(url($role === 'admin' ? 'admin' : ''));
    }

    public function adminHome(): Response
    {
        return $this->view('auth/dashboard', [
            'dashboardTitle' => 'Khu vực quản trị',
            'roleLabel' => 'Admin',
            'userName' => (string) ($_SESSION['user_name'] ?? ''),
            'role' => 'admin',
            'stats' => (new AdminDashboard(Database::connection($this->databaseConfig)))->overview(),
        ]);
    }

    public function customerHome(): Response
    {
        return Response::redirect(url());
    }
}
