<?php

namespace App\Repositories;

use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class WishlistRepository extends BaseRepository
{
    public function __construct(Wishlist $model)
    {
        parent::__construct($model);
    }

    public function getByUser(User $user): Collection
    {
        return $this->model->where('user_id', $user->id)
            ->with('product.images')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function exists(User $user, int $productId): bool
    {
        return $this->model->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();
    }

    public function toggle(User $user, int $productId): bool
    {
        $wishlist = $this->model->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return false;
        }

        $this->model->create([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);

        return true;
    }
}