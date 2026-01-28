<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function getStats(): array;

    public function getTableData(): array;

    public function getChartData(): array;

    public function deleteTableRow(int $id): bool;
}