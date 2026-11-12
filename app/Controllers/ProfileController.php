<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Environment;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\User;
use PDOException;
use RuntimeException;

final class ProfileController extends Controller
{
    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function showProfile(Request $request): Response
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $users  = new User(Database::connection($this->databaseConfig));
        $user   = $users->findById($userId);

        return $this->view('profile/show', [
            'user'    => $user,
            'errors'  => [],
            'success' => flash('success'),
            'error'   => flash('error'),
        ]);
    }

    public function updateProfile(Request $request): Response
    {
        $userId   = (int) ($_SESSION['user_id'] ?? 0);
        $input    = $request->body();
        $fullName = is_string($input['full_name'] ?? null) ? trim($input['full_name']) : '';
        $email    = is_string($input['email'] ?? null) ? strtolower(trim($input['email'])) : '';
        $phone    = is_string($input['phone'] ?? null) ? trim($input['phone']) : '';
        $errors   = [];

        if (!Validator::stringLength($fullName, 2, 100)) {
            $errors['full_name'] = 'Họ và tên phải có từ 2 đến 100 ký tự.';
        }
        if (!Validator::email($email) || strlen($email) > 150) {
            $errors['email'] = 'Vui lòng nhập email hợp lệ (tối đa 150 ký tự).';
        }
        if (!preg_match('/\A\+?[0-9][0-9\s().-]{6,19}\z/', $phone)) {
            $errors['phone'] = 'Vui lòng nhập số điện thoại hợp lệ (tối đa 20 ký tự).';
        }

        if ($errors !== []) {
            $users = new User(Database::connection($this->databaseConfig));
            return $this->view('profile/show', [
                'user'    => ['full_name' => $fullName, 'email' => $email, 'phone' => $phone],
                'errors'  => $errors,
                'success' => null,
                'error'   => null,
            ], 422);
        }

        try {
            $users = new User(Database::connection($this->databaseConfig));
            $users->updateProfile($userId, $fullName, $email, $phone);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $users = new User(Database::connection($this->databaseConfig));
                return $this->view('profile/show', [
                    'user'    => ['full_name' => $fullName, 'email' => $email, 'phone' => $phone],
                    'errors'  => ['email' => 'Email đã được sử dụng bởi tài khoản khác.'],
                    'success' => null,
                    'error'   => null,
                ], 422);
            }

            throw $exception;
        }

        $_SESSION['user_name'] = $fullName;
        flash('success', 'Thông tin cá nhân đã được cập nhật.');

        return Response::redirect(url('profile'));
    }

    public function showChangePassword(Request $request): Response
    {
        return $this->view('profile/change-password', [
            'errors'  => [],
            'success' => flash('success'),
            'error'   => flash('error'),
        ]);
    }

    public function changePassword(Request $request): Response
    {
        $userId      = (int) ($_SESSION['user_id'] ?? 0);
        $input       = $request->body();
        $currentPass = $input['current_password'] ?? null;
        $newPass     = $input['new_password'] ?? null;
        $confirmPass = $input['new_password_confirmation'] ?? null;
        $errors      = [];

        if (!is_string($currentPass) || $currentPass === '') {
            $errors['current_password'] = 'Vui lòng nhập mật khẩu hiện tại.';
        }
        if (!is_string($newPass) || strlen($newPass) < 8 || strlen($newPass) > 72) {
            $errors['new_password'] = 'Mật khẩu mới phải có từ 8 đến 72 byte.';
        }
        if (!is_string($confirmPass) || !is_string($newPass) || !hash_equals($newPass, $confirmPass)) {
            $errors['new_password_confirmation'] = 'Mật khẩu xác nhận không khớp.';
        }

        if ($errors !== []) {
            return $this->view('profile/change-password', [
                'errors'  => $errors,
                'success' => null,
                'error'   => null,
            ], 422);
        }

        $users        = new User(Database::connection($this->databaseConfig));
        $existingHash = $users->getPasswordHash($userId);

        if ($existingHash === null || !is_string($currentPass) || !password_verify($currentPass, $existingHash)) {
            return $this->view('profile/change-password', [
                'errors'  => ['current_password' => 'Mật khẩu hiện tại không chính xác.'],
                'success' => null,
                'error'   => null,
            ], 422);
        }

        $users->updatePassword($userId, password_hash((string) $newPass, PASSWORD_DEFAULT));
        flash('success', 'Mật khẩu đã được thay đổi thành công.');

        return Response::redirect(url('profile/change-password'));
    }

    public function showForgotPassword(Request $request): Response
    {
        return $this->view('profile/forgot-password', [
            'errors'    => [],
            'success'   => flash('success'),
            'error'     => flash('error'),
            'old'       => [],
        ]);
    }

    public function processForgotPassword(Request $request): Response
    {
        $input  = $request->body();
        $email  = is_string($input['email'] ?? null) ? strtolower(trim($input['email'])) : '';
        $errors = [];

        if (!Validator::email($email) || strlen($email) > 150) {
            $errors['email'] = 'Vui lòng nhập email hợp lệ.';
        }

        if ($errors !== []) {
            return $this->view('profile/forgot-password', [
                'errors'    => $errors,
                'old'       => ['email' => $email],
            ], 422);
        }

        $users = new User(Database::connection($this->databaseConfig));
        $user  = $users->findByEmail($email);
        if ($user !== null && $user['status'] === 'active') {
            $token = bin2hex(random_bytes(32));
            $users->createPasswordResetToken((int) $user['id'], $token);
            $resetUrl = url('reset-password/' . $token);

            if (Environment::get('APP_ENV', 'production') === 'local') {
                return Response::redirect($resetUrl);
            } else {
                $mailFrom = Environment::get('MAIL_FROM', '');
                if (!Validator::email($mailFrom)) {
                    $users->deleteUserResetTokens((int) $user['id']);
                    throw new RuntimeException('MAIL_FROM must be a valid email address to send password reset links.');
                }

                $message = "Xin chào {$user['full_name']},\n\n"
                    . "Bạn đã yêu cầu đặt lại mật khẩu AutoCare Hub. Mở liên kết sau trong vòng 1 giờ:\n"
                    . "{$resetUrl}\n\n"
                    . "Nếu bạn không yêu cầu thay đổi này, hãy bỏ qua email này.";
                $headers = "From: AutoCare Hub <{$mailFrom}>\r\n"
                    . "Content-Type: text/plain; charset=UTF-8";

                if (!mail($user['email'], 'AutoCare Hub password reset', $message, $headers)) {
                    $users->deleteUserResetTokens((int) $user['id']);
                    throw new RuntimeException('Unable to send password reset email.');
                }
            }
        }

        return $this->view('profile/forgot-password', [
            'errors'    => [],
            'old'       => [],
            'success'   => 'Nếu email thuộc tài khoản đang hoạt động, hướng dẫn đặt lại mật khẩu sẽ được gửi đến bạn.',
        ]);
    }

    public function showResetPassword(Request $request, array $parameters): Response
    {
        $token  = is_string($parameters['token'] ?? null) ? $parameters['token'] : '';
        $users  = new User(Database::connection($this->databaseConfig));
        $userId = $users->findValidResetToken($token);

        return $this->view('profile/reset-password', [
            'tokenValid' => $userId !== null,
            'token'      => $token,
            'errors'     => [],
        ]);
    }

    public function processResetPassword(Request $request, array $parameters): Response
    {
        $token  = is_string($parameters['token'] ?? null) ? $parameters['token'] : '';
        $users  = new User(Database::connection($this->databaseConfig));
        if ($users->findValidResetToken($token) === null) {
            return $this->view('profile/reset-password', [
                'tokenValid' => false,
                'token'      => $token,
                'errors'     => [],
            ]);
        }

        $input       = $request->body();
        $newPass     = $input['new_password'] ?? null;
        $confirmPass = $input['new_password_confirmation'] ?? null;
        $errors      = [];

        if (!is_string($newPass) || strlen($newPass) < 8 || strlen($newPass) > 72) {
            $errors['new_password'] = 'Mật khẩu phải có từ 8 đến 72 byte.';
        }
        if (!is_string($confirmPass) || !is_string($newPass) || !hash_equals($newPass, $confirmPass)) {
            $errors['new_password_confirmation'] = 'Mật khẩu xác nhận không khớp.';
        }

        if ($errors !== []) {
            return $this->view('profile/reset-password', [
                'tokenValid' => true,
                'token'      => $token,
                'errors'     => $errors,
            ], 422);
        }

        if (!$users->resetPasswordWithToken($token, password_hash($newPass, PASSWORD_DEFAULT))) {
            return $this->view('profile/reset-password', [
                'tokenValid' => false,
                'token'      => $token,
                'errors'     => [],
            ]);
        }

        flash('success', 'Mật khẩu đã được đặt lại. Vui lòng đăng nhập.');

        return Response::redirect(url('login'));
    }
}