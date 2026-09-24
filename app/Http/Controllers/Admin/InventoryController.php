<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\InventoryTransactionResource;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['category_id', 'brand_id', 'status', 'low_stock', 'low_stock_threshold', 'search', 'per_page']);
        $products = $this->inventoryService->getInventory($filters);
        return response()->json(ProductResource::collection($products));
    }

    public function lowStock(): JsonResponse
    {
        $threshold = request()->input('threshold', 10);
        $products = $this->inventoryService->getLowStockProducts($threshold);
        return response()->json(ProductResource::collection($products));
    }

    public function outOfStock(): JsonResponse
    {
        $products = $this->inventoryService->getOutOfStockProducts();
        return response()->json(ProductResource::collection($products));
    }

    public function adjustStock(int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|not_in:0',
            'note' => 'required|string|max:500',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $product = \App\Models\Product::findOrFail($id);
        $transaction = $this->inventoryService->adjustStock(
            $product,
            $request->input('quantity'),
            $request->input('note'),
            auth()->id(),
            $request->input('variant_id')
        );

        return response()->json(new InventoryTransactionResource($transaction->load(['product', 'variant', 'createdBy'])), 201);
    }

    public function transactions(): JsonResponse
    {
        $filters = request()->only(['product_id', 'type', 'date_from', 'date_to', 'per_page']);
        $transactions = $this->inventoryService->getTransactions($filters);
        return response()->json(InventoryTransactionResource::collection($transactions));
    }

    public function productTransactions(int $productId): JsonResponse
    {
        $filters = request()->only(['type', 'date_from', 'date_to', 'per_page']);
        $product = \App\Models\Product::findOrFail($productId);
        $transactions = $this->inventoryService->getTransactions(array_merge($filters, ['product_id' => $productId]));
        return response()->json(InventoryTransactionResource::collection($transactions));
    }
}