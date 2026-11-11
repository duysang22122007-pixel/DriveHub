<?php

declare(strict_types=1);

use App\Core\Environment;

$url = trim((string) Environment::get('APP_URL', ''));
if ($url === '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $url = rtrim(str_replace('\\', '/', dirname($scriptName)), '/.');

    if ($url === '/public') {
        $url = '';
    } elseif (str_ends_with($url, '/public')) {
        $url = substr($url, 0, -strlen('/public'));
    }
}

return [
    'name' => Environment::get('APP_NAME', 'DriveHub'),
    'environment' => Environment::get('APP_ENV', 'production'),
    'debug' => filter_var(Environment::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
    'url' => rtrim($url, '/'),
];
