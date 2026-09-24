<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Review;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Review $review): bool
    {
        return $review->is_approved || $user->id === $review->user_id || $user->hasPermission('settings.view');
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->hasPermission('settings.view');
    }

    public function approve(User $user, Review $review): bool
    {
        return $user->hasPermission('settings.view');
    }
}