<?php

class ProfileController
{
    public function index(): void
    {
        $customer = current_customer();
        $admin = current_admin();

        if ($customer !== null) {
            $account = require_role('customer');
        } elseif ($admin !== null) {
            $account = require_role('admin');
        } else {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $profile = (new AuthModel())->getAccountProfileByUserId((int) $account['user_id']);

        if ($profile === null) {
            http_response_code(404);
            exit('Không tìm thấy hồ sơ tài khoản.');
        }

        $title = 'Trang cá nhân - DriveHub';
        $view = 'profile';
        require_once PATH_VIEW_MAIN;
    }
}
