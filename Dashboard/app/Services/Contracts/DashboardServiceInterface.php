<?php

namespace App\Services\Contracts;

interface DashboardServiceInterface
{
    public function getStats(): array;

    public function getTableData(): array;

    public function getChartData(): array;

    public function deleteTableRow(int $id): array;
}