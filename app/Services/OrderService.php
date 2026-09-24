<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\InventoryTransactionType;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function getOrders(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Order::with(['user', 'items.product', 'payment', 'shipment']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', "%{$filters['search']}%")
                  ->orWhereHas('user', function ($uq) use ($filters) {
                      $uq->where('name', 'like', "%{$filters['search']}%")
                         ->orWhere('email', 'like', "%{$filters['search']}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function getOrder(int $id): ?Order
    {
        return Order::with(['user', 'items.product', 'items.variant', 'address', 'payment', 'shipment', 'coupon'])->find($id);
    }

    public function getOrderByNumber(string $orderNumber): ?Order
    {
        return Order::with(['user', 'items.product', 'items.variant', 'address', 'payment', 'shipment', 'coupon'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function updateOrderStatus(Order $order, OrderStatus $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            if (!$order->order_status->canTransitionTo($status)) {
                throw new \Exception("Cannot transition from {$order->order_status->value} to {$status->value}");
            }

            $order->update(['order_status' => $status]);

            if ($status === OrderStatus::Delivered) {
                $this->handleDelivered($order);
            } elseif ($status === OrderStatus::Cancelled) {
                $this->handleCancelled($order);
            } elseif ($status === OrderStatus::Refunded) {
                $this->handleRefunded($order);
            }

            return $order->fresh();
        });
    }

    public function updatePaymentStatus(Order $order, PaymentStatus $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            $order->update(['payment_status' => $status]);

            if ($status === PaymentStatus::Completed && $order->order_status === OrderStatus::Pending) {
                $order->update(['order_status' => OrderStatus::Confirmed]);
            }

            return $order->fresh();
        });
    }

    public function cancelOrder(Order $order, string $reason = ''): Order
    {
        return $this->updateOrderStatus($order, OrderStatus::Cancelled);
    }

    public function refundOrder(Order $order): Order
    {
        if (!$order->canBeRefunded()) {
            throw new \Exception('Order cannot be refunded');
        }

        return DB::transaction(function () use ($order) {
            $this->updateOrderStatus($order, OrderStatus::Refunded);
            $this->updatePaymentStatus($order, PaymentStatus::Refunded);

            $payment = $order->payment;
            if ($payment) {
                $payment->update(['status' => PaymentStatus::Refunded]);
            }

            return $order->fresh();
        });
    }

    protected function handleDelivered(Order $order): void
    {
        // Create shipment if not exists
        if (!$order->shipment) {
            Shipment::create([
                'order_id' => $order->id,
                'tracking_number' => 'DEL-' . $order->order_number,
                'carrier' => 'Internal',
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }
    }

    protected function handleCancelled(Order $order): void
    {
        // Restore inventory
        foreach ($order->items as $item) {
            if ($item->variant_id) {
                $variant = ProductVariant::find($item->variant_id);
                if ($variant) {
                    $variant->increment('stock', $item->qty);
                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'type' => InventoryTransactionType::Return,
                        'qty' => $item->qty,
                        'note' => "Order {$order->order_number} cancelled",
                        'created_by' => $order->user_id,
                    ]);
                }
            } else {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->qty);
                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'type' => InventoryTransactionType::Return,
                        'qty' => $item->qty,
                        'note' => "Order {$order->order_number} cancelled",
                        'created_by' => $order->user_id,
                    ]);
                }
            }
        }

        // Restore coupon usage
        if ($order->coupon) {
            $order->coupon->decrement('used_count');
        }
    }

    protected function handleRefunded(Order $order): void
    {
        // Same as cancelled for inventory
        $this->handleCancelled($order);
    }

    public function getOrderStats(): array
    {
        return [
            'total' => Order::count(),
            'pending' => Order::where('order_status', OrderStatus::Pending)->count(),
            'confirmed' => Order::where('order_status', OrderStatus::Confirmed)->count(),
            'processing' => Order::where('order_status', OrderStatus::Processing)->count(),
            'shipped' => Order::where('order_status', OrderStatus::Shipped)->count(),
            'delivered' => Order::where('order_status', OrderStatus::Delivered)->count(),
            'cancelled' => Order::where('order_status', OrderStatus::Cancelled)->count(),
            'total_revenue' => Order::where('payment_status', PaymentStatus::Completed)->sum('grand_total'),
        ];
    }
}