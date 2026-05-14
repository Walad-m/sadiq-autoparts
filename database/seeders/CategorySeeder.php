<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Oil & Fluids', 'description' => 'Engine oils, transmission fluids, brake fluids, coolants'],
            ['name' => 'Filters', 'description' => 'Oil filters, air filters, fuel filters, cabin filters'],
            ['name' => 'Brake System', 'description' => 'Brake pads, brake discs, brake shoes, brake fluid'],
            ['name' => 'Electrical', 'description' => 'Batteries, bulbs, fuses, alternators, starters'],
            ['name' => 'Engine Parts', 'description' => 'Spark plugs, belts, gaskets, valves, pistons'],
            ['name' => 'Suspension & Steering', 'description' => 'Shock absorbers, ball joints, tie rods, bushings'],
            ['name' => 'Tyres & Wheels', 'description' => 'Tyres, rims, wheel nuts, tyre tubes'],
            ['name' => 'Body Parts', 'description' => 'Mirrors, bumpers, headlights, wipers'],
            ['name' => 'Accessories', 'description' => 'Floor mats, seat covers, phone holders, air fresheners'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
