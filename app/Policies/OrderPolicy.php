<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('order.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->hasPermission('order.view');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermission('order.update');
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->id === $order->user_id) {
            return $order->canBeCancelled();
        }

        return $user->hasPermission('order.cancel');
    }

    public function refund(User $user, Order $order): bool
    {
        return $user->hasPermission('order.refund');
    }
}