<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Brand;

class BrandPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Brand $brand): bool
    {
        return $brand->isActive() || $user->hasPermission('brand.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('brand.create');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->hasPermission('brand.edit');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->hasPermission('brand.delete');
    }
}