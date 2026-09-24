<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ProductStatus;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('product.create');
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'sku' => 'required|string|max:100|unique:products,sku,' . $this->route('product')?->id,
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $this->route('product')?->id,
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', ProductStatus::values()),
            'images' => 'nullable|array',
            'images.*' => 'string|max:500',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required_with:variants|string|max:100|unique:product_variants,sku,' . $this->route('product')?->id,
            'variants.*.size' => 'nullable|string|max:50',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'This SKU is already in use.',
            'slug.unique' => 'This slug is already in use.',
            'special_price.lt' => 'Special price must be less than regular price.',
        ];
    }
}