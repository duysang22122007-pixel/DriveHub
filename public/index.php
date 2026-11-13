<?php

declare(strict_types=1);

use App\Core\Environment;
use App\Core\Request;
use App\Core\Router;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Helpers/functions.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

try {
    Environment::load(BASE_PATH . '/.env');
    $appConfig = require BASE_PATH . '/config/app.php';
    $databaseConfig = require BASE_PATH . '/config/database.php';
    $garageConfig = require BASE_PATH . '/config/garage.php';
    define('APP_URL', (string) $appConfig['url']);

    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        session_start([
            'use_strict_mode' => 1,
            'use_only_cookies' => 1,
        ]);
    }

    $router = new Router();
    $registerRoutes = require BASE_PATH . '/routes/web.php';
    $registerRoutes($router, $databaseConfig, $garageConfig);

    $router->dispatch(Request::fromGlobals())->send();
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');

    $isDatabaseError = $exception instanceof PDOException
        || str_contains($exception->getMessage(), 'SQLSTATE')
        || str_contains($exception->getMessage(), 'Unknown database')
        || str_contains($exception->getMessage(), 'Access denied');

    if (($appConfig['debug'] ?? false) === true) {
        echo '<h1>Application error</h1><pre>' . e($exception->getMessage()) . '</pre>';
    } elseif ($isDatabaseError) {
        echo '<h1>Không thể kết nối cơ sở dữ liệu</h1>'
            . '<p>Vui lòng kiểm tra lại <strong>DB_HOST</strong>, <strong>DB_NAME</strong>, <strong>DB_USERNAME</strong>, <strong>DB_PASSWORD</strong> trong <code>.env</code> và đảm bảo MySQL/Laragon đang chạy.</p>'
            . '<p>Chi tiết kỹ thuật: ' . e($exception->getMessage()) . '</p>';
    } else {
        echo '<h1>Đã xảy ra lỗi hệ thống</h1><p>Vui lòng thử lại sau.</p>';
    }
}
