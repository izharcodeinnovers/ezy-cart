<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Enums\PaymentMethod;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'coupon_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'grand_total',
        'payment_status',
        'order_status',
        'shipping_method',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'payment_status' => PaymentStatus::class,
            'order_status' => OrderStatus::class,
            'shipping_method' => ShippingMethod::class,
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function canBeCancelled(): bool
    {
        return $this->order_status->canTransitionTo(OrderStatus::Cancelled);
    }

    public function canBeRefunded(): bool
    {
        return $this->order_status === OrderStatus::Delivered && $this->payment_status === PaymentStatus::Completed;
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }
}