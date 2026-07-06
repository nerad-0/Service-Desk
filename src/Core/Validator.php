<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    public static function cleanString(mixed $value): string
    {
        return trim((string)$value);
    }

    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function length(string $value, int $min, int $max): bool
    {
        $length = mb_strlen($value);
        return $length >= $min && $length <= $max;
    }

    public static function enum(string $value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    public static function intInRange(mixed $value, int $min, int $max): bool
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return false;
        }

        $number = (int)$value;
        return $number >= $min && $number <= $max;
    }
}

