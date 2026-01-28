<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Repositories\Contracts\AssetRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AssetRepository implements AssetRepositoryInterface
{
    protected $model;

    public function __construct(Asset $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->with('category')->orderBy('created_at', 'desc')->get();
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with('category')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?Asset
    {
        return $this->model->with('category')->find($id);
    }

    public function create(array $data): Asset
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Asset
    {
        $asset = $this->find($id);
        if ($asset) {
            $asset->update($data);
            $asset->refresh()->load('category');
        }
        return $asset;
    }

    public function delete(int $id): bool
    {
        $asset = $this->find($id);
        return $asset ? $asset->delete() : false;
    }
}