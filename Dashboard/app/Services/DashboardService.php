<?php

namespace App\Services;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Services\Contracts\DashboardServiceInterface;

class DashboardService implements DashboardServiceInterface
{
    private $repository;

    public function __construct(DashboardRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getStats(): array
    {
        return $this->repository->getStats();
    }

    public function getTableData(): array
    {
        return $this->repository->getTableData();
    }

    public function getChartData(): array
    {
        return $this->repository->getChartData();
    }

    public function deleteTableRow(int $id): array
    {
        if ($id <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid ID provided',
                'id' => $id
            ];
        }

        $deleted = $this->repository->deleteTableRow($id);

        return [
            'success' => $deleted,
            'message' => $deleted ? 'Record deleted successfully' : 'Failed to delete record',
            'id' => $id
        ];
    }
}