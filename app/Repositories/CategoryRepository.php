<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getRootCategories(): Collection
    {
        return $this->model->whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getChildren(int $parentId): Collection
    {
        return $this->model->where('parent_id', $parentId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getTree(): Collection
    {
        return $this->model->with('childrenRecursive')
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getActivePaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('status', 'active')
            ->orderBy('name')
            ->paginate($perPage);
    }
}