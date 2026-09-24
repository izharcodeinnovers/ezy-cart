<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
        $this->authorizeResource(\App\Models\Product::class, 'product');
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['category_id', 'brand_id', 'status', 'search', 'min_price', 'max_price', 'in_stock', 'sort_by', 'sort_direction', 'per_page']);
        $products = $this->catalogService->getProducts($filters);
        return response()->json(ProductResource::collection($products));
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        $variants = $data['variants'] ?? [];
        unset($data['images'], $data['variants']);

        $product = $this->catalogService->createProduct($data);

        foreach ($images as $image) {
            $this->catalogService->addProductImage($product, ['path' => $image]);
        }

        foreach ($variants as $variant) {
            $this->catalogService->addProductVariant($product, $variant);
        }

        return response()->json(new ProductResource($product->load(['images', 'variants'])), 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(new ProductResource($product->load(['images', 'variants'])));
    }

    public function update(ProductRequest $request, int $id): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $data = $request->validated();
        $images = $data['images'] ?? [];
        $variants = $data['variants'] ?? [];
        unset($data['images'], $data['variants']);

        $product = $this->catalogService->updateProduct($product, $data);

        return response()->json(new ProductResource($product->load(['images', 'variants'])));
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->catalogService->deleteProduct($product);
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function addImage(int $id, ProductRequest $request): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $image = $this->catalogService->addProductImage($product, ['path' => $request->input('path')]);
        return response()->json($image, 201);
    }

    public function deleteImage(int $id, int $imageId): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $image = $product->images()->find($imageId);
        if (!$image) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        $this->catalogService->deleteProductImage($image);
        return response()->json(['message' => 'Image deleted successfully']);
    }

    public function reorderImages(int $id, ProductRequest $request): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->catalogService->reorderProductImages($product, $request->input('image_ids', []));
        return response()->json(['message' => 'Images reordered successfully']);
    }

    public function addVariant(int $id, ProductRequest $request): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $variant = $this->catalogService->addProductVariant($product, $request->validated());
        return response()->json($variant, 201);
    }

    public function updateVariant(int $id, int $variantId, ProductRequest $request): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $variant = $product->variants()->find($variantId);
        if (!$variant) {
            return response()->json(['message' => 'Variant not found'], 404);
        }

        $variant = $this->catalogService->updateProductVariant($variant, $request->validated());
        return response()->json($variant);
    }

    public function deleteVariant(int $id, int $variantId): JsonResponse
    {
        $product = $this->catalogService->getProduct($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $variant = $product->variants()->find($variantId);
        if (!$variant) {
            return response()->json(['message' => 'Variant not found'], 404);
        }

        $this->catalogService->deleteProductVariant($variant);
        return response()->json(['message' => 'Variant deleted successfully']);
    }
}