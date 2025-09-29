<?php

namespace App\Enums;

enum OrderType: string
{
    case CLIENT = 'client';
    case SUPPLIER = 'supplier';

    public static function validate(string $value): bool
    {
        return in_array($value, self::values());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
