<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Category;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::all()->keyBy('slug');

        $assets = [
            ['name' => 'Dell Latitude Laptop', 'serial_number' => 'DL-001', 'category' => 'electronics', 'value' => 1200.00, 'status' => 'active', 'purchase_date' => '2024-01-10'],
            ['name' => 'HP LaserJet Printer', 'serial_number' => 'HP-002', 'category' => 'electronics', 'value' => 450.00, 'status' => 'active', 'purchase_date' => '2024-02-15'],
            ['name' => 'Office Desk', 'serial_number' => null, 'category' => 'furniture', 'value' => 350.00, 'status' => 'active', 'purchase_date' => '2024-01-05'],
            ['name' => 'Ergonomic Chair', 'serial_number' => null, 'category' => 'furniture', 'value' => 280.00, 'status' => 'active', 'purchase_date' => '2024-01-05'],
            ['name' => 'Company Van', 'serial_number' => 'VAN-001', 'category' => 'vehicles', 'value' => 25000.00, 'status' => 'active', 'purchase_date' => '2023-06-20'],
            ['name' => 'Forklift', 'serial_number' => 'FL-001', 'category' => 'equipment', 'value' => 15000.00, 'status' => 'active', 'purchase_date' => '2023-08-10'],
            ['name' => 'Microsoft 365 License', 'serial_number' => 'MS365-001', 'category' => 'software', 'value' => 99.00, 'status' => 'active', 'purchase_date' => '2024-01-01'],
            ['name' => 'Warehouse Building', 'serial_number' => null, 'category' => 'real-estate', 'value' => 500000.00, 'status' => 'active', 'purchase_date' => '2020-03-15'],
            ['name' => 'MacBook Pro', 'serial_number' => 'MBP-003', 'category' => 'electronics', 'value' => 2499.00, 'status' => 'active', 'purchase_date' => '2024-03-01'],
            ['name' => 'Meeting Room Table', 'serial_number' => null, 'category' => 'furniture', 'value' => 800.00, 'status' => 'active', 'purchase_date' => '2024-02-01'],
            ['name' => 'Delivery Truck', 'serial_number' => 'TRK-002', 'category' => 'vehicles', 'value' => 35000.00, 'status' => 'maintenance', 'purchase_date' => '2022-11-05'],
            ['name' => 'Adobe Creative Cloud', 'serial_number' => 'ADB-001', 'category' => 'software', 'value' => 54.99, 'status' => 'active', 'purchase_date' => '2024-01-15'],
        ];

        foreach ($assets as $assetData) {
            $category = $categories->get($assetData['category']);
            if (!$category) {
                continue;
            }

            Asset::firstOrCreate(
                [
                    'name' => $assetData['name'],
                    'category_id' => $category->id,
                ],
                [
                    'serial_number' => $assetData['serial_number'],
                    'value' => $assetData['value'],
                    'status' => $assetData['status'],
                    'purchase_date' => $assetData['purchase_date'],
                    'description' => null,
                ]
            );
        }
    }
}
