<?php

namespace App\Repositories;

use App\Repositories\Contracts\DashboardRepositoryInterface;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getStats(): array
    {
        usleep(500000); // 0.5 seconds

        // Mock data - Replace with actual database queries
        return [
            'total_users' => 12543,
            'users_trend' => 'up',
            'users_trend_value' => '+12.5%',
            'total_orders' => 3421,
            'orders_trend' => 'up',
            'orders_trend_value' => '+8.3%',
            'revenue' => 125430.50,
            'revenue_trend' => 'up',
            'revenue_trend_value' => '+15.2%',
            'growth' => 23.5,
            'growth_trend' => 'up',
            'growth_trend_value' => '+5.1%',
        ];
    }

    public function getTableData(): array
    {
        // Simulate API delay
        usleep(300000); // 0.3 seconds

        // Mock data - Replace with actual database queries
        return [
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'status' => 'active',
                'created_at' => '2024-01-15T10:30:00Z'
            ],
            [
                'id' => 2,
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'status' => 'active',
                'created_at' => '2024-01-16T11:20:00Z'
            ],
            [
                'id' => 3,
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@example.com',
                'status' => 'inactive',
                'created_at' => '2024-01-17T09:15:00Z'
            ],
            [
                'id' => 4,
                'name' => 'Alice Williams',
                'email' => 'alice.williams@example.com',
                'status' => 'active',
                'created_at' => '2024-01-18T14:45:00Z'
            ],
            [
                'id' => 5,
                'name' => 'Charlie Brown',
                'email' => 'charlie.brown@example.com',
                'status' => 'active',
                'created_at' => '2024-01-19T16:20:00Z'
            ],
            [
                'id' => 6,
                'name' => 'Diana Prince',
                'email' => 'diana.prince@example.com',
                'status' => 'active',
                'created_at' => '2024-01-20T08:30:00Z'
            ],
            [
                'id' => 7,
                'name' => 'Edward Norton',
                'email' => 'edward.norton@example.com',
                'status' => 'inactive',
                'created_at' => '2024-01-21T12:10:00Z'
            ],
            [
                'id' => 8,
                'name' => 'Fiona Apple',
                'email' => 'fiona.apple@example.com',
                'status' => 'active',
                'created_at' => '2024-01-22T15:25:00Z'
            ],
            [
                'id' => 9,
                'name' => 'George Clooney',
                'email' => 'george.clooney@example.com',
                'status' => 'active',
                'created_at' => '2024-01-23T10:50:00Z'
            ],
            [
                'id' => 10,
                'name' => 'Helen Mirren',
                'email' => 'helen.mirren@example.com',
                'status' => 'active',
                'created_at' => '2024-01-24T13:40:00Z'
            ],
            [
                'id' => 11,
                'name' => 'Ian McKellen',
                'email' => 'ian.mckellen@example.com',
                'status' => 'inactive',
                'created_at' => '2024-01-25T11:15:00Z'
            ],
            [
                'id' => 12,
                'name' => 'Julia Roberts',
                'email' => 'julia.roberts@example.com',
                'status' => 'active',
                'created_at' => '2024-01-26T09:30:00Z'
            ],
        ];
    }

    public function getChartData(): array
    {

        usleep(400000); // 0.4 seconds

        // Mock data - Replace with actual database queries
        return [
            'sales' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'datasets' => [
                    [
                        'label' => 'Sales',
                        'data' => [65, 59, 80, 81, 56, 55, 40, 45, 60, 70, 75, 85],
                        'borderColor' => 'rgb(75, 192, 192)',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'tension' => 0.4
                    ],
                    [
                        'label' => 'Revenue',
                        'data' => [45, 50, 65, 70, 48, 52, 38, 42, 55, 65, 70, 80],
                        'borderColor' => 'rgb(255, 99, 132)',
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'tension' => 0.4
                    ]
                ]
            ],
            'revenue' => [
                'labels' => ['Electronics', 'Clothing', 'Food', 'Books', 'Toys', 'Sports'],
                'datasets' => [
                    [
                        'label' => 'Revenue',
                        'data' => [45000, 32000, 28000, 15000, 12000, 18000],
                        'backgroundColor' => [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)'
                        ],
                        'borderColor' => [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        'borderWidth' => 1
                    ]
                ]
            ],
            'distribution' => [
                'labels' => ['Active Users', 'Inactive Users', 'Pending Users', 'Suspended Users'],
                'datasets' => [
                    [
                        'label' => 'User Distribution',
                        'data' => [65, 20, 10, 5],
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
        usleep(200000); // 0.2 seconds

        // Mock deletion - Replace with actual database deletion
        // In a real application, you would do:
        // $record = Model::findOrFail($id);
        // return $record->delete();

        return true;
    }
}