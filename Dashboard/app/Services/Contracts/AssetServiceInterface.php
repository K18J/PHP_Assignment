<?php

namespace App\Services\Contracts;

use App\Models\Asset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Collection;

interface AssetServiceInterface
{
    public function getAll(): Collection;

    public function getPaginated(int $perPage = 10): LengthAwarePaginator;

    public function getById(int $id): ?Asset;

    public function validate(array $data, ?int $id = null): Validator;

    public function create(array $data): Asset;

    public function update(int $id, array $data): Asset;

    public function delete(int $id): bool;
}
