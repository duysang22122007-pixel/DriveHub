<?php

declare(strict_types=1);

use App\Core\Environment;

return [
    'driver' => 'mysql',
    'host' => Environment::get('DB_HOST', '127.0.0.1'),
    'port' => (int) Environment::get('DB_PORT', '3306'),
    'name' => Environment::get('DB_NAME', 'autocare_hub'),
    'username' => Environment::get('DB_USERNAME', 'root'),
    'password' => Environment::get('DB_PASSWORD', ''),
];
