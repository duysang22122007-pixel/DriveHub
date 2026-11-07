<?php

if (!function_exists('debug')) {
    function debug($data)
    {
        echo '<pre>';
        print_r($data);
        die;
    }
}

if (!function_exists('upload_file')) {
    function upload_file($folder, $file)
    {
        $targetFile = $folder . '/' . time() . '-' . $file["name"];

        if (move_uploaded_file($file["tmp_name"], PATH_ASSETS_UPLOADS . $targetFile)) {
            return $targetFile;
        }

        throw new Exception('Upload file không thành công!');
    }
}

if (!function_exists('current_customer')) {
    function current_customer(): ?array
    {
        $customer = $_SESSION['customer'] ?? null;

        return is_array($customer) ? $customer : null;
    }
}

if (!function_exists('current_admin')) {
    function current_admin(): ?array
    {
        $admin = $_SESSION['admin'] ?? null;

        return is_array($admin) ? $admin : null;
    }
}

if (!function_exists('checkCustomerAuth')) {
    function checkCustomerAuth(): array
    {
        $customer = current_customer();

        if ($customer === null || !isset($customer['user_id'], $customer['customer_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        return $customer;
    }
}

if (!function_exists('checkAdminAuth')) {
    function checkAdminAuth(): array
    {
        $admin = current_admin();

        if ($admin === null || !isset($admin['user_id'], $admin['employee_id'])
            || ($admin['role'] ?? '') !== 'admin'
        ) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        return $admin;
    }
}

if (!function_exists('require_role')) {
    function require_role(string $role): array
    {
        if ($role === 'customer') {
            return checkCustomerAuth();
        }

        if ($role === 'admin') {
            return checkAdminAuth();
        }

        http_response_code(403);
        exit('Bạn không có quyền truy cập chức năng này.');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token(string $token): bool
    {
        return isset($_SESSION['csrf_token'])
            && is_string($_SESSION['csrf_token'])
            && $token !== ''
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}