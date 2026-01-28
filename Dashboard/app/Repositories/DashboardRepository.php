<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getStats(): array
    {
        usleep(300000);

        $totalAssets = Asset::count();
        $totalValue = (float) Asset::sum('value');
        $activeCount = Asset::where('status', 'active')->count();
        $growth = $totalAssets > 0 ? round(($activeCount / $totalAssets) * 100, 1) : 0;

        return [
            'total_users' => $totalAssets,
            'users_trend' => 'up',
            'users_trend_value' => '+0%',
            'total_orders' => $activeCount,
            'orders_trend' => 'up',
            'orders_trend_value' => '+0%',
            'revenue' => $totalValue,
            'revenue_trend' => 'up',
            'revenue_trend_value' => '+0%',
            'growth' => $growth,
            'growth_trend' => 'up',
            'growth_trend_value' => '+0%',
        ];
    }

    public function getTableData(): array
    {
        usleep(200000);


        return Asset::with('category')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'category' => $asset->category->name ?? '-',
                    'status' => $asset->status,
                    'created_at' => $asset->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }

    public function getChartData(): array
    {
        usleep(200000);

        $revenueByCategory = Asset::query()
            ->selectRaw('category_id, sum(value) as total')
            ->groupBy('category_id')
            ->get();
        $categories = \App\Models\Category::orderBy('name')->get()->keyBy('id');
        $revenueLabels = [];
        $revenueData = [];
        $colors = [
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 99, 132, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
        ];
        $borderColors = [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
        ];
        $i = 0;
        foreach ($revenueByCategory as $row) {
            $cat = $categories->get($row->category_id);
            $revenueLabels[] = $cat ? $cat->name : 'Unknown';
            $revenueData[] = (float) $row->total;
            $i++;
        }

        $statusCounts = Asset::selectRaw('status, count(*) as count')->groupBy('status')->get();
        $statusLabels = $statusCounts->pluck('status')->map(function ($s) {
            return ucfirst($s);
        })->toArray();
        $statusData = $statusCounts->pluck('count')->toArray();

        return [
            'sales' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'datasets' => [
                    [
                        'label' => 'Assets Value Trend',
                        'data' => array_fill(0, 12, 0),
                        'borderColor' => 'rgb(75, 192, 192)',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'tension' => 0.4
                    ],
                ]
            ],
            'revenue' => [
                'labels' => $revenueLabels ?: ['No data'],
                'datasets' => [
                    [
                        'label' => 'Value by Category',
                        'data' => $revenueData ?: [0],
                        'backgroundColor' => array_slice($colors, 0, count($revenueLabels)),
                        'borderColor' => array_slice($borderColors, 0, count($revenueLabels)),
                        'borderWidth' => 1
                    ]
                ]
            ],
            'distribution' => [
                'labels' => $statusLabels ?: ['No data'],
                'datasets' => [
                    [
                        'label' => 'Asset Status',
                        'data' => $statusData ?: [0],
                        'backgroundColor' => [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(108, 117, 125, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ],
                        'borderColor' => [
                            'rgba(40, 167, 69, 1)',
                            'rgba(108, 117, 125, 1)',
                            'rgba(255, 193, 7, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        'borderWidth' => 2
                    ]
                ]
            ]
        ];
    }

    public function deleteTableRow(int $id): bool
    {
        $asset = Asset::find($id);
        return $asset ? $asset->delete() : false;
    }
}