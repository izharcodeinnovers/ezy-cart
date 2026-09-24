<?php

namespace App\Repositories;

use App\Models\Review;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReviewRepository extends BaseRepository
{
    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    public function getApprovedByProduct(Product $product): Collection
    {
        return $product->reviews()->where('is_approved', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->model->where('is_approved', false)
            ->with(['user', 'product'])
            ->orderBy('created_at')
            ->get();
    }

    public function getByUser(User $user): Collection
    {
        return $this->model->where('user_id', $user->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getProductAverageRating(Product $product): float
    {
        return $product->reviews()->where('is_approved', true)->avg('rating') ?? 0;
    }

    public function getProductReviewCount(Product $product): int
    {
        return $product->reviews()->where('is_approved', true)->count();
    }
}