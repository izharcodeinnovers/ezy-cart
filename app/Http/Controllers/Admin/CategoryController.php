<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected CatalogService $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
        $this->authorizeResource(\App\Models\Category::class, 'category');
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['status', 'search', 'per_page']);
        $categories = $this->catalogService->getCategories($filters);
        return response()->json(CategoryResource::collection($categories));
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $this->catalogService->createCategory($request->validated());
        return response()->json(new CategoryResource($category), 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->catalogService->getCategory($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json(new CategoryResource($category->load('children')));
    }

    public function update(CategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->catalogService->getCategory($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category = $this->catalogService->updateCategory($category, $request->validated());
        return response()->json(new CategoryResource($category));
    }

    public function destroy(int $id): JsonResponse
    {
        $category = $this->catalogService->getCategory($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $this->catalogService->deleteCategory($category);
        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function tree(): JsonResponse
    {
        $categories = \App\Models\Category::with('childrenRecursive')
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json(CategoryResource::collection($categories));
    }
}