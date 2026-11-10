<?php

declare(strict_types=1);

use App\Core\Environment;

return [
    'name' => Environment::get('APP_NAME', 'AutoCare Hub'),
    'environment' => Environment::get('APP_ENV', 'production'),
    'debug' => filter_var(Environment::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
    'url' => rtrim((string) Environment::get('APP_URL', ''), '/'),
];
