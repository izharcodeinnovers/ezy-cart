<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AddressType;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:' . implode(',', AddressType::values()),
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'postcode' => 'required|string|max:20',
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ];
    }
}