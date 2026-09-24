<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Enums\InventoryTransactionType;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function getInventory(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Product::with(['variants', 'category', 'brand']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['low_stock'])) {
            $query->where('stock', '<=', $filters['low_stock_threshold'] ?? 10);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 15);
    }

    public function adjustStock(Product $product, int $quantity, string $note, int $userId, ?int $variantId = null): InventoryTransaction
    {
        return DB::transaction(function () use ($product, $quantity, $note, $userId, $variantId) {
            $type = $quantity > 0 ? InventoryTransactionType::Adjustment : InventoryTransactionType::Adjustment;

            if ($variantId) {
                $variant = ProductVariant::findOrFail($variantId);
                $variant->increment('stock', $quantity);
                $product->increment('stock', $quantity);

                return InventoryTransaction::create([
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'type' => $type,
                    'qty' => abs($quantity),
                    'note' => $note,
                    'created_by' => $userId,
                ]);
            }

            $product->increment('stock', $quantity);

            return InventoryTransaction::create([
                'product_id' => $product->id,
                'type' => $type,
                'qty' => abs($quantity),
                'note' => $note,
                'created_by' => $userId,
            ]);
        });
    }

    public function recordSale(Order $order): void
    {
        foreach ($order->items as $item) {
            $type = InventoryTransactionType::Sale;

            if ($item->variant_id) {
                $variant = ProductVariant::find($item->variant_id);
                if ($variant) {
                    $variant->decrement('stock', $item->qty);
                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'type' => $type,
                        'qty' => $item->qty,
                        'note' => "Sale from order {$order->order_number}",
                        'created_by' => $order->user_id,
                    ]);
                }
            } else {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('stock', $item->qty);
                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'type' => $type,
                        'qty' => $item->qty,
                        'note' => "Sale from order {$order->order_number}",
                        'created_by' => $order->user_id,
                    ]);
                }
            }
        }
    }

    public function recordPurchase(Product $product, int $quantity, string $note, int $userId, ?int $variantId = null): InventoryTransaction
    {
        return DB::transaction(function () use ($product, $quantity, $note, $userId, $variantId) {
            if ($variantId) {
                $variant = ProductVariant::findOrFail($variantId);
                $variant->increment('stock', $quantity);
                $product->increment('stock', $quantity);
            } else {
                $product->increment('stock', $quantity);
            }

            return InventoryTransaction::create([
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'type' => InventoryTransactionType::Purchase,
                'qty' => $quantity,
                'note' => $note,
                'created_by' => $userId,
            ]);
        });
    }

    public function getTransactions(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = InventoryTransaction::with(['product', 'variant', 'createdBy']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function getLowStockProducts(int $threshold = 10): \Illuminate\Support\Collection
    {
        return Product::where('status', 'active')
            ->where('stock', '<=', $threshold)
            ->with(['category', 'brand'])
            ->get();
    }

    public function getOutOfStockProducts(): \Illuminate\Support\Collection
    {
        return Product::where('status', 'active')
            ->where('stock', 0)
            ->with(['category', 'brand'])
            ->get();
    }
}