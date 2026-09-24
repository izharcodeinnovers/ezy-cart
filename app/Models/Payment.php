<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_method',
        'amount',
        'status',
        'gateway_response',
    ];

    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'status' => PaymentStatus::class,
            'gateway_response' => 'json',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => PaymentStatus::Completed]);
        $this->order->update([
            'payment_status' => PaymentStatus::Completed,
            'order_status' => OrderStatus::Confirmed,
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => PaymentStatus::Failed]);
        $this->order->update(['payment_status' => PaymentStatus::Failed]);
    }
}