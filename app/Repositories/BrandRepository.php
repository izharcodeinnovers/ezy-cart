<?php

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandRepository extends BaseRepository
{
    public function __construct(Brand $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getBySlug(string $slug): ?Brand
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