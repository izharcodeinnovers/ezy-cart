<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $filters = request()->only(['status', 'per_page']);
        $orders = auth()->user()->orders()->with(['items.product', 'payment', 'shipment']);

        if (!empty($filters['status'])) {
            $orders->where('order_status', $filters['status']);
        }

        $orders = $orders->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 10);
        return response()->json(OrderResource::collection($orders));
    }

    public function show(int $id): JsonResponse
    {
        $order = auth()->user()->orders()->with(['items.product', 'items.variant', 'address', 'payment', 'shipment', 'coupon'])->find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json(new OrderResource($order));
    }

    public function cancel(int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $order = auth()->user()->orders()->find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (!$order->canBeCancelled()) {
            return response()->json(['message' => 'Order cannot be cancelled'], 422);
        }

        $order->update(['order_status' => \App\Enums\OrderStatus::Cancelled]);
        return response()->json(new OrderResource($order->load(['items.product', 'payment', 'shipment'])));
    }
}