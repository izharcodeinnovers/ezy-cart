<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Return = 'return';
    case Adjustment = 'adjustment';
    case Transfer = 'transfer';
    case Damage = 'damage';
    case Loss = 'loss';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Sale => 'Sale',
            self::Return => 'Return',
            self::Adjustment => 'Adjustment',
            self::Transfer => 'Transfer',
            self::Damage => 'Damage',
            self::Loss => 'Loss',
        };
    }

    public function isInbound(): bool
    {
        return in_array($this, [self::Purchase, self::Return, self::Transfer]);
    }

    public function isOutbound(): bool
    {
        return in_array($this, [self::Sale, self::Damage, self::Loss]);
    }

    public function isNeutral(): bool
    {
        return $this === self::Adjustment;
    }
}