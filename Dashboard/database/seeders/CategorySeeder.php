<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and equipment'],
            ['name' => 'Furniture', 'slug' => 'furniture', 'description' => 'Office and workspace furniture'],
            ['name' => 'Vehicles', 'slug' => 'vehicles', 'description' => 'Company vehicles and transport'],
            ['name' => 'Equipment', 'slug' => 'equipment', 'description' => 'Machinery and tools'],
            ['name' => 'Software', 'slug' => 'software', 'description' => 'Software licenses and subscriptions'],
            ['name' => 'Real Estate', 'slug' => 'real-estate', 'description' => 'Property and buildings'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
