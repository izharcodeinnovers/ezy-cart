<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository extends BaseRepository
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function getByTransactionId(string $transactionId): ?Payment
    {
        return $this->model->where('transaction_id', $transactionId)->first();
    }

    public function getByOrder(int $orderId): ?Payment
    {
        return $this->model->where('order_id', $orderId)->first();
    }

    public function getCompletedPayments(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->where('status', 'completed')->with('order.user');

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function getFailedPayments(): Collection
    {
        return $this->model->where('status', 'failed')
            ->with('order.user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}