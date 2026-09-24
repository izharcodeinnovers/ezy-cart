<?php

namespace App\Enums;

enum AddressType: string
{
    case Billing = 'billing';
    case Shipping = 'shipping';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Billing => 'Billing',
            self::Shipping => 'Shipping',
        };
    }
}