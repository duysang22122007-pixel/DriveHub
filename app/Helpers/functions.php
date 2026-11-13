<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(string|int|float|bool|null $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = defined('APP_URL') ? APP_URL : '';

        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_url')) {
    function asset_url(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);
        if (preg_match('#^(?:https?:)?//#i', $path) === 1) {
            return $path;
        }

        return url($path);
    }
}

if (!function_exists('service_image_url')) {
    function service_image_url(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim($path);
        if (preg_match('#^(?:https?:)?//#i', $path) === 1) {
            return $path;
        }

        $relative = ltrim($path, '/');
        if (str_contains($relative, '..') || !str_starts_with($relative, 'assets/images/')) {
            return null;
        }

        $publicRoot = dirname(__DIR__, 2) . '/public/';
        if (!is_file($publicRoot . $relative)) {
            return null;
        }

        return url($relative);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('An active session is required for CSRF tokens.');
        }

        if (!isset($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_token_is_valid')) {
    function csrf_token_is_valid(mixed $submittedToken): bool
    {
        return is_string($submittedToken)
            && isset($_SESSION['_csrf_token'])
            && is_string($_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $submittedToken);
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;

            return null;
        }

        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return is_string($value) ? $value : null;
    }
}
