<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function required(mixed $value): bool
    {
        if ($value === null || $value === false) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }

    public static function email(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function stringLength(mixed $value, int $minimum, int $maximum): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);

        return $length >= $minimum && $length <= $maximum;
    }

    /**
     * @param list<string> $allowed
     */
    public static function oneOf(mixed $value, array $allowed): bool
    {
        return is_string($value) && in_array($value, $allowed, true);
    }
}
