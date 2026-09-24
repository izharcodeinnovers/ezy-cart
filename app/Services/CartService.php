<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getCart(?User $user = null): Cart
    {
        $user = $user ?? Auth::user();

        if ($user) {
            return $this->getCustomerCart($user);
        }

        return $this->getGuestCart();
    }

    public function getCustomerCart(User $user): Cart
    {
        return $user->cart()->firstOrCreate([]);
    }

    public function getGuestCart(): Cart
    {
        $sessionId = session()->getId();
        return Cart::firstOrCreate(['user_id' => null, 'id' => $sessionId]);
    }

    public function addItem(int $productId, int $quantity = 1, ?int $variantId = null, ?User $user = null): CartItem
    {
        $cart = $this->getCart($user);
        $product = Product::findOrFail($productId);

        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            $price = $variant->price;
        } else {
            $variant = null;
            $price = $product->getCurrentPrice();
        }

        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $existingItem->increment('qty', $quantity);
            return $existingItem->fresh();
        }

        return $cart->items()->create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'qty' => $quantity,
            'price' => $price,
        ]);
    }

    public function updateItem(CartItem $item, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            $item->delete();
            return $item;
        }

        $item->update(['qty' => $quantity]);
        return $item->fresh();
    }

    public function removeItem(CartItem $item): bool
    {
        return $item->delete();
    }

    public function clearCart(?User $user = null): void
    {
        $cart = $this->getCart($user);
        $cart->items()->delete();
    }

    public function getCartItems(?User $user = null): Collection
    {
        $cart = $this->getCart($user);
        return $cart->items()->with(['product', 'variant'])->get();
    }

    public function getCartTotal(?User $user = null): float
    {
        $cart = $this->getCart($user);
        return $cart->getSubtotal();
    }

    public function mergeCarts(User $user): void
    {
        $guestCart = $this->getGuestCart();
        $customerCart = $this->getCustomerCart($user);

        foreach ($guestCart->items as $guestItem) {
            $existingItem = $customerCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('variant_id', $guestItem->variant_id)
                ->first();

            if ($existingItem) {
                $existingItem->increment('qty', $guestItem->qty);
            } else {
                $guestItem->update(['cart_id' => $customerCart->id]);
            }
        }

        $guestCart->delete();
    }

    public function validateStock(?User $user = null): array
    {
        $items = $this->getCartItems($user);
        $errors = [];

        foreach ($items as $item) {
            $stock = $item->variant ? $item->variant->stock : $item->product->stock;
            if ($item->qty > $stock) {
                $errors[] = "Insufficient stock for {$item->product->name}. Available: {$stock}";
            }
        }

        return $errors;
    }
}