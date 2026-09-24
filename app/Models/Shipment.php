<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'tracking_number',
        'carrier',
        'status',
        'tracking_data',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'tracking_data' => 'json',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markAsShipped(): void
    {
        $this->update([
            'status' => 'in_transit',
            'shipped_at' => now(),
        ]);
        $this->order->update(['order_status' => 'shipped']);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
        $this->order->update(['order_status' => 'delivered']);
    }
}