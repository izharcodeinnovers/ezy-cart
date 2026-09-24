<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return $product->isActive() || $user->hasPermission('product.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('product.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermission('product.edit');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermission('product.delete');
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->hasPermission('product.edit');
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasPermission('product.delete');
    }
}