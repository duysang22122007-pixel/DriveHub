<?php

declare(strict_types=1);

use App\Core\Environment;

return [
    'phone' => Environment::get('GARAGE_PHONE'),
    'address' => Environment::get('GARAGE_ADDRESS'),
    'hours' => Environment::get('GARAGE_HOURS'),
];
