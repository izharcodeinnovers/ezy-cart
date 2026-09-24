<?php

namespace App\Repositories;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryTransactionRepository extends BaseRepository
{
    public function __construct(InventoryTransaction $model)
    {
        parent::__construct($model);
    }

    public function getByProduct(Product $product, array $filters = []): LengthAwarePaginator
    {
        $query = $product->inventoryTransactions()->with(['variant', 'createdBy']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function getByType(string $type, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['product', 'variant', 'createdBy'])
            ->where('type', $type);

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function getRecent(int $limit = 50): Collection
    {
        return $this->model->with(['product', 'variant', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}