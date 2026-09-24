<?php

namespace App\Repositories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CouponRepository extends BaseRepository
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    public function getByCode(string $code): ?Coupon
    {
        return $this->model->where('code', $code)->first();
    }

    public function getValidCoupons(): Collection
    {
        $now = now();
        return $this->model->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhere('used_count', '<', 'usage_limit');
            })
            ->get();
    }

    public function getActivePaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}