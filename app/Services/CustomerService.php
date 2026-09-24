<?php

namespace App\Services;

use App\Models\User;
use App\Models\Address;
use App\Models\Wishlist;
use App\Models\Review;
use App\Models\Order;
use App\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getCustomers(array $filters = []): LengthAwarePaginator
    {
        $query = User::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('phone', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function getCustomer(int $id): ?User
    {
        return User::with(['addresses', 'orders', 'wishlists.product', 'reviews.product'])->find($id);
    }

    public function createCustomer(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['status'] = $data['status'] ?? UserStatus::Active->value;
        return User::create($data);
    }

    public function updateCustomer(User $customer, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $customer->update($data);
        return $customer->fresh();
    }

    public function deleteCustomer(User $customer): bool
    {
        return $customer->delete();
    }

    public function changeStatus(User $customer, UserStatus $status): User
    {
        $customer->update(['status' => $status]);
        return $customer->fresh();
    }

    public function assignRole(User $customer, string $role): void
    {
        $customer->assignRole($role);
    }

    public function removeRole(User $customer, string $role): void
    {
        $customer->removeRole($role);
    }

    public function getAddresses(User $customer): \Illuminate\Support\Collection
    {
        return $customer->addresses()->orderBy('is_default', 'desc')->get();
    }

    public function createAddress(User $customer, array $data): Address
    {
        // If this is the first address or is_default is true, unset other defaults
        if ($data['is_default'] ?? false) {
            $customer->addresses()->where('type', $data['type'])->update(['is_default' => false]);
        } elseif ($customer->addresses()->where('type', $data['type'])->count() === 0) {
            $data['is_default'] = true;
        }

        return $customer->addresses()->create($data);
    }

    public function updateAddress(Address $address, array $data): Address
    {
        if ($data['is_default'] ?? false) {
            $address->user->addresses()->where('type', $address->type)->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);
        return $address->fresh();
    }

    public function deleteAddress(Address $address): bool
    {
        $wasDefault = $address->is_default;
        $result = $address->delete();

        if ($wasDefault) {
            $newDefault = $address->user->addresses()->where('type', $address->type)->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return $result;
    }

    public function getWishlist(User $customer): \Illuminate\Support\Collection
    {
        return $customer->wishlists()->with('product.images')->get();
    }

    public function addToWishlist(User $customer, int $productId): Wishlist
    {
        return Wishlist::firstOrCreate([
            'user_id' => $customer->id,
            'product_id' => $productId,
        ]);
    }

    public function removeFromWishlist(User $customer, int $productId): bool
    {
        return Wishlist::where('user_id', $customer->id)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    public function getReviews(User $customer): \Illuminate\Support\Collection
    {
        return $customer->reviews()->with('product')->get();
    }

    public function createReview(User $customer, array $data): Review
    {
        return $customer->reviews()->create($data);
    }

    public function getOrders(User $customer, array $filters = []): LengthAwarePaginator
    {
        $query = $customer->orders()->with(['items.product', 'payment', 'shipment']);

        if (!empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 10);
    }

    public function getCustomerStats(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('status', UserStatus::Active)->count(),
            'inactive' => User::where('status', UserStatus::Inactive)->count(),
            'with_orders' => User::has('orders')->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];
    }
}