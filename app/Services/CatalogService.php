<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CatalogService
{
    public function getCategories(array $filters = []): LengthAwarePaginator
    {
        $query = Category::with('children')->whereNull('parent_id');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 15);
    }

    public function getCategory(int $id): ?Category
    {
        return Category::with(['children', 'products'])->find($id);
    }

    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->fresh();
    }

    public function deleteCategory(Category $category): bool
    {
        return $category->delete();
    }

    public function getBrands(array $filters = []): LengthAwarePaginator
    {
        $query = Brand::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 15);
    }

    public function getBrand(int $id): ?Brand
    {
        return Brand::with('products')->find($id);
    }

    public function createBrand(array $data): Brand
    {
        return Brand::create($data);
    }

    public function updateBrand(Brand $brand, array $data): Brand
    {
        $brand->update($data);
        return $brand->fresh();
    }

    public function deleteBrand(Brand $brand): bool
    {
        return $brand->delete();
    }

    public function getProducts(array $filters = []): LengthAwarePaginator
    {
        $query = Product::with(['category', 'brand', 'images', 'variants']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
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

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getProduct(int $id): ?Product
    {
        return Product::with(['category', 'brand', 'images', 'variants'])->find($id);
    }

    public function getProductBySlug(string $slug): ?Product
    {
        return Product::with(['category', 'brand', 'images', 'variants'])->where('slug', $slug)->first();
    }

    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }

    public function addProductImage(Product $product, array $data): ProductImage
    {
        $maxPosition = $product->images()->max('position') ?? 0;
        $data['position'] = $maxPosition + 1;
        return $product->images()->create($data);
    }

    public function deleteProductImage(ProductImage $image): bool
    {
        return $image->delete();
    }

    public function reorderProductImages(Product $product, array $imageIds): void
    {
        foreach ($imageIds as $index => $id) {
            $product->images()->where('id', $id)->update(['position' => $index + 1]);
        }
    }

    public function addProductVariant(Product $product, array $data): ProductVariant
    {
        return $product->variants()->create($data);
    }

    public function updateProductVariant(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);
        return $variant->fresh();
    }

    public function deleteProductVariant(ProductVariant $variant): bool
    {
        return $variant->delete();
    }

    public function getFeaturedProducts(int $limit = 8): Collection
    {
        return Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}