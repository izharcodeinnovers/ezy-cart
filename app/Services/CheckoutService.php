<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Enums\PaymentMethod;

class CheckoutService
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function calculateTotals(Cart $cart, ?Coupon $coupon = null, ?Address $address = null, string $shippingMethod = ShippingMethod::Standard->value): array
    {
        $subtotal = $cart->getSubtotal();
        $discountAmount = 0;
        $taxAmount = 0;
        $shippingAmount = $this->calculateShipping($subtotal, $shippingMethod, $address);

        if ($coupon && $coupon->isValid()) {
            $discountAmount = $coupon->calculateDiscount($subtotal);
        }

        // Simple tax calculation (10%)
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = $taxableAmount * 0.10;

        $grandTotal = $subtotal - $discountAmount + $taxAmount + $shippingAmount;

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'grand_total' => $grandTotal,
        ];
    }

    protected function calculateShipping(float $subtotal, string $shippingMethod, ?Address $address): float
    {
        // Free shipping over $100
        if ($subtotal >= 100) {
            return 0;
        }

        return match ($shippingMethod) {
            ShippingMethod::Standard->value => 9.99,
            ShippingMethod::Express->value => 19.99,
            ShippingMethod::Overnight->value => 29.99,
            ShippingMethod::Pickup->value => 0,
            default => 9.99,
        };
    }

    public function createOrder(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $cart = $this->cartService->getCustomerCart($user);

            if ($cart->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            $coupon = null;
            if (!empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', $data['coupon_code'])->first();
            }

            $address = Address::findOrFail($data['address_id']);

            $totals = $this->calculateTotals($cart, $coupon, $address, $data['shipping_method'] ?? ShippingMethod::Standard->value);

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'address_id' => $address->id,
                'coupon_id' => $coupon?->id,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_amount' => $totals['tax_amount'],
                'shipping_amount' => $totals['shipping_amount'],
                'grand_total' => $totals['grand_total'],
                'payment_status' => PaymentStatus::Pending,
                'order_status' => OrderStatus::Pending,
                'shipping_method' => $data['shipping_method'] ?? ShippingMethod::Standard->value,
                'payment_method' => $data['payment_method'] ?? PaymentMethod::CashOnDelivery->value,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'price' => $item->price,
                    'qty' => $item->qty,
                    'total' => $item->price * $item->qty,
                ]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            $this->cartService->clearCart($user);

            return $order->load(['items.product', 'address', 'coupon']);
        });
    }

    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid('', true)), 0, 6));
        return "{$prefix}-{$date}-{$random}";
    }
}