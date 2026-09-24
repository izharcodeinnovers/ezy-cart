<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Address;

class AddressPolicy
{
    public function viewAny(User $user): bool
    {
        return false; // Addresses are private
    }

    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    public function delete(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }
}