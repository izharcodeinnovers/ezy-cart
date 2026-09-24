<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        return $category->isActive() || $user->hasPermission('category.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('category.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission('category.edit');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermission('category.delete');
    }
}