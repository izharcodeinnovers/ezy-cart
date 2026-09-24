<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Services\CustomerService;
use App\Enums\UserStatus;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
        $this->authorizeResource(\App\Models\User::class, 'user');
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['status', 'search', 'role', 'per_page']);
        $users = $this->customerService->getCustomers($filters);
        return response()->json(UserResource::collection($users));
    }

    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->customerService->createCustomer($request->validated());

        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles'));
        }

        return response()->json(new UserResource($user->load('roles')), 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->customerService->getCustomer($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(new UserResource($user->load(['addresses', 'orders', 'wishlists.product', 'reviews.product', 'roles'])));
    }

    public function update(UserRequest $request, int $id): JsonResponse
    {
        $user = $this->customerService->getCustomer($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $data = $request->validated();
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        $user = $this->customerService->updateCustomer($user, $data);

        if ($roles !== null) {
            $user->syncRoles($roles);
        }

        return response()->json(new UserResource($user->load('roles')));
    }

    public function destroy(int $id): JsonResponse
    {
        $user = $this->customerService->getCustomer($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete yourself'], 403);
        }

        $this->customerService->deleteCustomer($user);
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function changeStatus(int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', UserStatus::values()),
        ]);

        $user = $this->customerService->getCustomer($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot change your own status'], 403);
        }

        $user = $this->customerService->changeStatus($user, UserStatus::from($request->input('status')));
        return response()->json(new UserResource($user->load('roles')));
    }

    public function stats(): JsonResponse
    {
        $stats = $this->customerService->getCustomerStats();
        return response()->json($stats);
    }
}