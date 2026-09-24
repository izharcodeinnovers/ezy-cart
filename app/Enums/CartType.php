<?php

namespace App\Enums;

enum CartType: string
{
    case Guest = 'guest';
    case Customer = 'customer';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Guest => 'Guest',
            self::Customer => 'Customer',
        };
    }
}