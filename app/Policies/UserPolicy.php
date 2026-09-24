<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('customer.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermission('customer.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('customer.create') || false; // Customers can register themselves
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasPermission('customer.edit');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('customer.delete') && $user->id !== $model->id;
    }

    public function changeStatus(User $user, User $model): bool
    {
        return $user->hasPermission('customer.edit') && $user->id !== $model->id;
    }
}