<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            $productId = $this->input('product_id');

            // Check if user has purchased this product
            $hasPurchased = $user->orders()
                ->whereHas('items', function ($q) use ($productId) {
                    $q->where('product_id', $productId);
                })
                ->where('payment_status', 'completed')
                ->exists();

            if (!$hasPurchased) {
                $validator->errors()->add('product_id', 'You can only review products you have purchased.');
            }

            // Check if user already reviewed this product
            $existingReview = $user->reviews()->where('product_id', $productId)->exists();
            if ($existingReview) {
                $validator->errors()->add('product_id', 'You have already reviewed this product.');
            }
        });
    }
}