<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AddressRepository extends BaseRepository
{
    public function __construct(Address $model)
    {
        parent::__construct($model);
    }

    public function getByUser(User $user): Collection
    {
        return $this->model->where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->get();
    }

    public function getDefaultBilling(User $user): ?Address
    {
        return $this->model->where('user_id', $user->id)
            ->where('type', 'billing')
            ->where('is_default', true)
            ->first();
    }

    public function getDefaultShipping(User $user): ?Address
    {
        return $this->model->where('user_id', $user->id)
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first();
    }
}