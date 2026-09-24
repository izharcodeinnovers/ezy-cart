<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Packed = 'packed';
    case Shipped = 'shipped';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Packed => 'Packed',
            self::Shipped => 'Shipped',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function isActive(): bool
    {
        return !in_array($this, [self::Cancelled, self::Refunded, self::Delivered]);
    }

    public function isCompleted(): bool
    {
        return $this === self::Delivered;
    }

    public function canTransitionTo(self $newStatus): bool
    {
        $transitions = [
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Processing, self::Cancelled],
            self::Processing => [self::Packed, self::Cancelled],
            self::Packed => [self::Shipped, self::Cancelled],
            self::Shipped => [self::OutForDelivery],
            self::OutForDelivery => [self::Delivered, self::Cancelled],
            self::Delivered => [self::Refunded],
            self::Cancelled => [],
            self::Refunded => [],
        ];

        return in_array($newStatus, $transitions[$this] ?? []);
    }
}