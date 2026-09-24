<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';
    case Archived = 'archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Draft => 'Draft',
            self::Archived => 'Archived',
        };
    }

    public function isVisible(): bool
    {
        return $this === self::Active;
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Active, self::Inactive, self::Draft]);
    }
}