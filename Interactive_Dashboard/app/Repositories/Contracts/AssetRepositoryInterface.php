<?php

namespace App\Repositories\Contracts;

use App\Models\Asset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AssetRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): ?Asset;

    public function create(array $data): Asset;

    public function update(int $id, array $data): Asset;

    public function delete(int $id): bool;
}
