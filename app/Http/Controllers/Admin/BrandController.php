<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
        $this->authorizeResource(\App\Models\Brand::class, 'brand');
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['status', 'search', 'per_page']);
        $brands = $this->catalogService->getBrands($filters);
        return response()->json(BrandResource::collection($brands));
    }

    public function store(BrandRequest $request): JsonResponse
    {
        $brand = $this->catalogService->createBrand($request->validated());
        return response()->json(new BrandResource($brand), 201);
    }

    public function show(int $id): JsonResponse
    {
        $brand = $this->catalogService->getBrand($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }
        return response()->json(new BrandResource($brand->load('products')));
    }

    public function update(BrandRequest $request, int $id): JsonResponse
    {
        $brand = $this->catalogService->getBrand($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $brand = $this->catalogService->updateBrand($brand, $request->validated());
        return response()->json(new BrandResource($brand));
    }

    public function destroy(int $id): JsonResponse
    {
        $brand = $this->catalogService->getBrand($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $this->catalogService->deleteBrand($brand);
        return response()->json(['message' => 'Brand deleted successfully']);
    }
}