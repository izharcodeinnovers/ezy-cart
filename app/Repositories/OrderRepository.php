<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getByUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->orders()->with(['items.product', 'payment', 'shipment']);

        if (!empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 10);
    }

    public function getByNumber(string $orderNumber): ?Order
    {
        return $this->model->with(['user', 'items.product', 'items.variant', 'address', 'payment', 'shipment', 'coupon'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPendingOrders(): Collection
    {
        return $this->model->where('order_status', 'pending')
            ->with(['user', 'items.product'])
            ->orderBy('created_at')
            ->get();
    }

    public function getOrdersByStatus(string $status): Collection
    {
        return $this->model->where('order_status', $status)
            ->with(['user', 'items.product'])
            ->orderBy('created_at')
            ->get();
    }
}