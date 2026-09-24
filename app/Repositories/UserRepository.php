<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getByPhone(string $phone): ?User
    {
        return $this->model->where('phone', $phone)->first();
    }

    public function getCustomers(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->role('customer');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('phone', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function getAdmins(): Collection
    {
        return $this->model->role(['super_admin', 'admin', 'manager'])->get();
    }

    public function getWithOrders(): Collection
    {
        return $this->model->has('orders')->with('orders')->get();
    }
}