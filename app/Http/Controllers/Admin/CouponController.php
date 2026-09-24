<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Coupon::class, 'coupon');
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['per_page']);
        $coupons = Coupon::query();

        if (!empty($filters['search'])) {
            $coupons->where('code', 'like', "%{$filters['search']}%");
        }

        return response()->json(CouponResource::collection($coupons->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15)));
    }

    public function store(CouponRequest $request): JsonResponse
    {
        $coupon = Coupon::create($request->validated());
        return response()->json(new CouponResource($coupon), 201);
    }

    public function show(int $id): JsonResponse
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }
        return response()->json(new CouponResource($coupon));
    }

    public function update(CouponRequest $request, int $id): JsonResponse
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        $coupon->update($request->validated());
        return response()->json(new CouponResource($coupon));
    }

    public function destroy(int $id): JsonResponse
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        $coupon->delete();
        return response()->json(['message' => 'Coupon deleted successfully']);
    }
}