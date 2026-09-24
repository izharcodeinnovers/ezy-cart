<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Coupon;

class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('settings.view'); // Only admins can manage coupons
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $user->hasPermission('settings.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('settings.edit');
    }

    public function update(User $user, Coupon $coupon): bool
    {
        return $user->hasPermission('settings.edit');
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $user->hasPermission('settings.edit');
    }
}