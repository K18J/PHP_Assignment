<?php

namespace App\Services;

use App\Models\Asset;
use App\Repositories\Contracts\AssetRepositoryInterface;
use App\Services\Contracts\AssetServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

class AssetService implements AssetServiceInterface
{
    private $repository;

    public function __construct(AssetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function getById(int $id): ?Asset
    {
        return $this->repository->find($id);
    }

    public function validate(array $data, ?int $id = null): ValidatorContract
    {
        $rules = [
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'value' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive,maintenance,retired',
            'description' => 'nullable|string',
            'purchase_date' => 'nullable|date',
        ];
        return Validator::make($data, $rules);
    }

    public function create(array $data): Asset
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Asset
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
