<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Environment
{
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new RuntimeException('Unable to read environment configuration.');
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                throw new RuntimeException('Invalid environment configuration line.');
            }

            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!preg_match('/\A[A-Z][A-Z0-9_]*\z/', $name)) {
                throw new RuntimeException('Invalid environment variable name.');
            }

            if ((str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

           if (getenv($name) === false || getenv($name) === '') {
              putenv($name . '=' . $value);
              $_ENV[$name] = $value;
            }
        }
    }

    public static function get(string $name, ?string $default = null): ?string
    {
        $value = getenv($name);

        return $value === false ? $default : $value;
    }
}
