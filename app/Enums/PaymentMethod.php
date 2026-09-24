<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case PayPal = 'paypal';
    case Stripe = 'stripe';
    case BankTransfer = 'bank_transfer';
    case CashOnDelivery = 'cash_on_delivery';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::CreditCard => 'Credit Card',
            self::DebitCard => 'Debit Card',
            self::PayPal => 'PayPal',
            self::Stripe => 'Stripe',
            self::BankTransfer => 'Bank Transfer',
            self::CashOnDelivery => 'Cash on Delivery',
        };
    }

    public function isOnline(): bool
    {
        return in_array($this, [self::CreditCard, self::DebitCard, self::PayPal, self::Stripe, self::BankTransfer]);
    }

    public function requiresGateway(): bool
    {
        return $this->isOnline();
    }
}