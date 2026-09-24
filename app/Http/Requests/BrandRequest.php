<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('brand.create');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,' . $this->route('brand')?->id,
            'logo' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ];
    }
}