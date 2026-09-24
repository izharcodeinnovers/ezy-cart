<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CartRepository extends BaseRepository
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function getByUser(User $user): ?Cart
    {
        return $this->model->where('user_id', $user->id)->first();
    }

    public function getGuestCart(string $sessionId): ?Cart
    {
        return $this->model->where('user_id', null)->where('id', $sessionId)->first();
    }

    public function getWithItems(int $cartId): ?Cart
    {
        return $this->model->with(['items.product', 'items.variant'])->find($cartId);
    }
}