<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('category.create');
    }

    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $this->route('category')?->id,
            'description' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ];
    }
}