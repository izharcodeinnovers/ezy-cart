<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json(BrandResource::collection($brands));
    }

    public function show(string $slug): JsonResponse
    {
        $brand = Brand::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $products = $brand->products()
            ->where('status', 'active')
            ->with(['category', 'images'])
            ->paginate(request()->input('per_page', 15));

        return response()->json([
            'brand' => new BrandResource($brand),
            'products' => \App\Http\Resources\ProductResource::collection($products),
        ]);
    }
}