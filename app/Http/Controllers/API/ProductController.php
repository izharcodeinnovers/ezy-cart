<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\BrandResource;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $filters = request()->only(['category_id', 'brand_id', 'search', 'min_price', 'max_price', 'in_stock', 'sort_by', 'sort_direction', 'per_page']);
        $query = Product::where('status', 'active')->with(['category', 'brand', 'images', 'variants']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('sku', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['in_stock'])) {
            $query->where('stock', '>', 0);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $products = $query->paginate($filters['per_page'] ?? 15);
        return response()->json(ProductResource::collection($products));
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with(['category', 'brand', 'images', 'variants'])
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $related = Product::where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return response()->json([
            'product' => new ProductResource($product),
            'related_products' => ProductResource::collection($related),
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->limit(8)
            ->get();

        return response()->json(ProductResource::collection($products));
    }

    public function search(): JsonResponse
    {
        $query = request()->input('q', '');
        if (strlen($query) < 2) {
            return response()->json(ProductResource::collection(collect()));
        }

        $products = Product::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json(ProductResource::collection($products));
    }
}