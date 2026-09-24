<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartItemResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(): JsonResponse
    {
        $cart = $this->cartService->getCart();
        return response()->json(new CartResource($cart->load(['items.product', 'items.variant'])));
    }

    public function add(CartRequest $request): JsonResponse
    {
        $item = $this->cartService->addItem(
            $request->input('product_id'),
            $request->input('qty', 1),
            $request->input('variant_id')
        );

        return response()->json(new CartItemResource($item->load(['product', 'variant'])), 201);
    }

    public function update(int $id): JsonResponse
    {
        $request->validate([
            'qty' => 'required|integer|min:0|max:99',
        ]);

        $item = auth()->user()->cart->items()->find($id);
        if (!$item) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $item = $this->cartService->updateItem($item, $request->input('qty'));
        return response()->json(new CartItemResource($item->load(['product', 'variant'])));
    }

    public function remove(int $id): JsonResponse
    {
        $item = auth()->user()->cart->items()->find($id);
        if (!$item) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $this->cartService->removeItem($item);
        return response()->json(['message' => 'Item removed from cart']);
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clearCart();
        return response()->json(['message' => 'Cart cleared']);
    }

    public function validateStock(): JsonResponse
    {
        $errors = $this->cartService->validateStock();
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        return response()->json(['message' => 'All items in stock']);
    }
}