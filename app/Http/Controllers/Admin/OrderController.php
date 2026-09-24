<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected PaymentService $paymentService;

    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->authorizeResource(\App\Models\Order::class, 'order');
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['user_id', 'order_status', 'payment_status', 'date_from', 'date_to', 'search', 'per_page']);
        $orders = $this->orderService->getOrders($filters);
        return response()->json(OrderResource::collection($orders));
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json(new OrderResource($order));
    }

    public function showByNumber(string $orderNumber): JsonResponse
    {
        $order = $this->orderService->getOrderByNumber($orderNumber);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json(new OrderResource($order));
    }

    public function update(OrderRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order = $this->orderService->updateOrderStatus($order, OrderStatus::tryFrom($request->input('order_status')) ?? $order->order_status);
        return response()->json(new OrderResource($order));
    }

    public function updateStatus(int $id): JsonResponse
    {
        $request->validate([
            'order_status' => 'required|in:' . implode(',', OrderStatus::values()),
        ]);

        $order = $this->orderService->getOrder($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order = $this->orderService->updateOrderStatus($order, OrderStatus::from($request->input('order_status')));
        return response()->json(new OrderResource($order));
    }

    public function cancel(int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $order = $this->orderService->getOrder($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order = $this->orderService->cancelOrder($order, $request->input('reason'));
        return response()->json(new OrderResource($order));
    }

    public function refund(int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order = $this->orderService->refundOrder($order);
        return response()->json(new OrderResource($order));
    }

    public function stats(): JsonResponse
    {
        $stats = $this->orderService->getOrderStats();
        return response()->json($stats);
    }
}