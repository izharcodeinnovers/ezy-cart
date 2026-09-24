<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ShippingMethod;
use App\Enums\PaymentMethod;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'address_id' => 'required|exists:addresses,id',
            'coupon_code' => 'nullable|string|max:50|exists:coupons,code',
            'shipping_method' => 'required|in:' . implode(',', ShippingMethod::values()),
            'payment_method' => 'required|in:' . implode(',', PaymentMethod::values()),
            'notes' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cart = $this->user()->cart;
            if (!$cart || $cart->isEmpty()) {
                $validator->errors()->add('cart', 'Your cart is empty.');
            }

            $errors = app(\App\Services\CartService::class)->validateStock($this->user());
            foreach ($errors as $error) {
                $validator->errors()->add('stock', $error);
            }
        });
    }
}