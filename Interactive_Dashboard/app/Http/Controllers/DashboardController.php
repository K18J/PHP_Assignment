<?php

namespace App\Http\Controllers;

use App\Services\Contracts\DashboardServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private $dashboardService;

    public function __construct(DashboardServiceInterface $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): View
    {
        return view('dashboard');
    }

    public function getStats(): JsonResponse
    {
        $stats = $this->dashboardService->getStats();
        return response()->json($stats);
    }

    public function getTableData(): JsonResponse
    {
        $data = $this->dashboardService->getTableData();
        return response()->json($data);
    }

    public function getChartData(): JsonResponse
    {
        $chartData = $this->dashboardService->getChartData();
        return response()->json($chartData);
    }

    public function deleteTableRow(int $id): JsonResponse
    {
        $result = $this->dashboardService->deleteTableRow($id);
        return response()->json($result);
    }
}