<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    protected CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function calculate(): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'coupon_code' => 'nullable|string|max:50|exists:coupons,code',
            'shipping_method' => 'required|in:standard,express,overnight,pickup',
        ]);

        $cart = auth()->user()->cart;
        if (!$cart || $cart->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $coupon = null;
        if ($request->input('coupon_code')) {
            $coupon = \App\Models\Coupon::where('code', $request->input('coupon_code'))->first();
        }

        $address = \App\Models\Address::findOrFail($request->input('address_id'));
        $totals = $this->checkoutService->calculateTotals($cart, $coupon, $address, $request->input('shipping_method'));

        return response()->json($totals);
    }

    public function placeOrder(CheckoutRequest $request): JsonResponse
    {
        $order = $this->checkoutService->createOrder(auth()->user(), $request->validated());
        return response()->json(new OrderResource($order), 201);
    }
}