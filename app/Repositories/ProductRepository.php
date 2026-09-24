<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getActive(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->where('status', 'active')
            ->with(['category', 'brand', 'images', 'variants']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['in_stock'])) {
            $query->where('stock', '>', 0);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getBySlug(string $slug): ?Product
    {
        return $this->model->with(['category', 'brand', 'images', 'variants'])
            ->where('slug', $slug)
            ->first();
    }

    public function getFeatured(int $limit = 8): Collection
    {
        return $this->model->where('status', 'active')
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function getRelated(Product $product, int $limit = 4): Collection
    {
        return $this->model->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function getLowStock(int $threshold = 10): Collection
    {
        return $this->model->where('status', 'active')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get();
    }

    public function getOutOfStock(): Collection
    {
        return $this->model->where('status', 'active')
            ->where('stock', 0)
            ->get();
    }

    public function search(string $query, int $limit = 10): Collection
    {
        return $this->model->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();
    }
}