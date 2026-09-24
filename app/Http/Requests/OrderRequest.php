<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Enums\PaymentMethod;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('order.view');
    }

    public function rules(): array
    {
        return [
            'order_number' => 'required|string|max:50|unique:orders,order_number,' . $this->route('order')?->id,
            'user_id' => 'required|exists:users,id',
            'address_id' => 'required|exists:addresses,id',
            'coupon_id' => 'nullable|exists:coupons,id',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'payment_status' => 'required|in:' . implode(',', PaymentStatus::values()),
            'order_status' => 'required|in:' . implode(',', OrderStatus::values()),
            'shipping_method' => 'required|in:' . implode(',', ShippingMethod::values()),
            'payment_method' => 'required|in:' . implode(',', PaymentMethod::values()),
            'notes' => 'nullable|string',
        ];
    }
}