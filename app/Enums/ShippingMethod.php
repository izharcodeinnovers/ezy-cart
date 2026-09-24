<?php

namespace App\Enums;

enum ShippingMethod: string
{
    case Standard = 'standard';
    case Express = 'express';
    case Overnight = 'overnight';
    case Pickup = 'pickup';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard Shipping',
            self::Express => 'Express Shipping',
            self::Overnight => 'Overnight Shipping',
            self::Pickup => 'Store Pickup',
        };
    }

    public function estimatedDays(): int
    {
        return match ($this) {
            self::Standard => 5,
            self::Express => 2,
            self::Overnight => 1,
            self::Pickup => 0,
        };
    }
}