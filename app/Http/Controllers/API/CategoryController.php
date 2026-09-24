<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();

        return response()->json(CategoryResource::collection($categories));
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('status', 'active')
            ->with('children')
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $products = $category->products()
            ->where('status', 'active')
            ->with(['brand', 'images'])
            ->paginate(request()->input('per_page', 15));

        return response()->json([
            'category' => new CategoryResource($category),
            'products' => \App\Http\Resources\ProductResource::collection($products),
        ]);
    }

    public function all(): JsonResponse
    {
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json(CategoryResource::collection($categories));
    }
}