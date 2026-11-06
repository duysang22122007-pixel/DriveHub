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

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        $user = $_SESSION['user'] ?? null;

        return is_array($user) ? $user : null;
    }
}

if (!function_exists('require_role')) {
    function require_role(string $role): array
    {
        $user = current_user();

        if ($user === null) {
            header('Location: ' . BASE_URL . '?action=auth&mode=login');
            exit;
        }

        if (($user['role'] ?? '') !== $role) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập chức năng này.');
        }

        return $user;
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