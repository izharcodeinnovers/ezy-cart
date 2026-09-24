<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CouponType;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('settings.edit');
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:coupons,code,' . $this->route('coupon')?->id,
            'type' => 'required|in:' . implode(',', CouponType::values()),
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}