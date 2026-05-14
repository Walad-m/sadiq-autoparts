<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');
        $suppliers = Supplier::pluck('id', 'name');

        $products = [
            ['name' => 'Oil Filter — Toyota Corolla', 'part_number' => 'OF-TC-001', 'category' => 'Filters', 'supplier' => 'Abossey Okai Wholesale', 'unit' => 'piece', 'cost_price' => 12, 'selling_price' => 20, 'quantity' => 45, 'reorder_level' => 10],
            ['name' => 'Oil Filter — Nissan Almera', 'part_number' => 'OF-NA-002', 'category' => 'Filters', 'supplier' => 'Abossey Okai Wholesale', 'unit' => 'piece', 'cost_price' => 14, 'selling_price' => 25, 'quantity' => 30, 'reorder_level' => 10],
            ['name' => 'Air Filter — Universal', 'part_number' => 'AF-UNI-003', 'category' => 'Filters', 'supplier' => 'China Auto Parts', 'unit' => 'piece', 'cost_price' => 8, 'selling_price' => 15, 'quantity' => 60, 'reorder_level' => 15],
            ['name' => 'Brake Pads (Front) — Toyota', 'part_number' => 'BP-TF-004', 'category' => 'Brake System', 'supplier' => 'Japan Motors Parts', 'unit' => 'set', 'cost_price' => 55, 'selling_price' => 90, 'quantity' => 12, 'reorder_level' => 5],
            ['name' => 'Brake Pads (Rear) — Nissan', 'part_number' => 'BP-NR-005', 'category' => 'Brake System', 'supplier' => 'Japan Motors Parts', 'unit' => 'set', 'cost_price' => 50, 'selling_price' => 85, 'quantity' => 3, 'reorder_level' => 5],
            ['name' => 'Brake Disc — Toyota Camry', 'part_number' => 'BD-TC-006', 'category' => 'Brake System', 'supplier' => 'Silver Star Auto', 'unit' => 'piece', 'cost_price' => 120, 'selling_price' => 200, 'quantity' => 4, 'reorder_level' => 3],
            ['name' => 'Engine Oil 5W-30 (4L)', 'part_number' => 'EO-5W30-007', 'category' => 'Oil & Fluids', 'supplier' => 'Abossey Okai Wholesale', 'unit' => 'piece', 'cost_price' => 85, 'selling_price' => 140, 'quantity' => 25, 'reorder_level' => 8],
            ['name' => 'ATF Fluid 1 Litre — Honda', 'part_number' => 'ATF-H-008', 'category' => 'Oil & Fluids', 'supplier' => 'Japan Motors Parts', 'unit' => 'litre', 'cost_price' => 18, 'selling_price' => 30, 'quantity' => 30, 'reorder_level' => 10],
            ['name' => 'Brake Fluid DOT 4 (500ml)', 'part_number' => 'BF-D4-009', 'category' => 'Oil & Fluids', 'supplier' => 'Abossey Okai Wholesale', 'unit' => 'piece', 'cost_price' => 12, 'selling_price' => 22, 'quantity' => 40, 'reorder_level' => 10],
            ['name' => 'Coolant Green 1L', 'part_number' => 'CL-G-010', 'category' => 'Oil & Fluids', 'supplier' => 'China Auto Parts', 'unit' => 'litre', 'cost_price' => 10, 'selling_price' => 18, 'quantity' => 35, 'reorder_level' => 10],
            ['name' => 'Spark Plug Set x4 — Bosch', 'part_number' => 'SP-B4-011', 'category' => 'Engine Parts', 'supplier' => 'Silver Star Auto', 'unit' => 'set', 'cost_price' => 25, 'selling_price' => 45, 'quantity' => 8, 'reorder_level' => 5],
            ['name' => 'Fan Belt — Toyota', 'part_number' => 'FB-T-012', 'category' => 'Engine Parts', 'supplier' => 'Azar Trading', 'unit' => 'piece', 'cost_price' => 15, 'selling_price' => 28, 'quantity' => 20, 'reorder_level' => 5],
            ['name' => 'Timing Belt — Honda Civic', 'part_number' => 'TB-HC-013', 'category' => 'Engine Parts', 'supplier' => 'Japan Motors Parts', 'unit' => 'piece', 'cost_price' => 45, 'selling_price' => 80, 'quantity' => 6, 'reorder_level' => 3],
            ['name' => 'Car Battery 55AH — Leoch', 'part_number' => 'CB-L55-014', 'category' => 'Electrical', 'supplier' => 'Abossey Okai Wholesale', 'unit' => 'piece', 'cost_price' => 280, 'selling_price' => 420, 'quantity' => 6, 'reorder_level' => 3],
            ['name' => 'Car Battery 75AH — Varta', 'part_number' => 'CB-V75-015', 'category' => 'Electrical', 'supplier' => 'Silver Star Auto', 'unit' => 'piece', 'cost_price' => 380, 'selling_price' => 550, 'quantity' => 2, 'reorder_level' => 2],
            ['name' => 'Headlight Bulb H4 Pair', 'part_number' => 'HB-H4-016', 'category' => 'Electrical', 'supplier' => 'China Auto Parts', 'unit' => 'pair', 'cost_price' => 8, 'selling_price' => 15, 'quantity' => 50, 'reorder_level' => 10],
            ['name' => 'Shock Absorber (Front) — Toyota', 'part_number' => 'SA-TF-017', 'category' => 'Suspension & Steering', 'supplier' => 'Azar Trading', 'unit' => 'piece', 'cost_price' => 90, 'selling_price' => 160, 'quantity' => 5, 'reorder_level' => 3],
            ['name' => 'Ball Joint — Nissan', 'part_number' => 'BJ-N-018', 'category' => 'Suspension & Steering', 'supplier' => 'Azar Trading', 'unit' => 'piece', 'cost_price' => 35, 'selling_price' => 60, 'quantity' => 10, 'reorder_level' => 4],
            ['name' => 'Wiper Blade 22" Universal', 'part_number' => 'WB-22-019', 'category' => 'Body Parts', 'supplier' => 'China Auto Parts', 'unit' => 'piece', 'cost_price' => 6, 'selling_price' => 12, 'quantity' => 0, 'reorder_level' => 10],
            ['name' => 'Side Mirror — Toyota (Left)', 'part_number' => 'SM-TL-020', 'category' => 'Body Parts', 'supplier' => 'Azar Trading', 'unit' => 'piece', 'cost_price' => 40, 'selling_price' => 70, 'quantity' => 3, 'reorder_level' => 2],
            ['name' => 'Floor Mat Set — Universal', 'part_number' => 'FM-UNI-021', 'category' => 'Accessories', 'supplier' => 'China Auto Parts', 'unit' => 'set', 'cost_price' => 20, 'selling_price' => 40, 'quantity' => 15, 'reorder_level' => 5],
            ['name' => 'Phone Holder — Dashboard', 'part_number' => 'PH-D-022', 'category' => 'Accessories', 'supplier' => 'China Auto Parts', 'unit' => 'piece', 'cost_price' => 5, 'selling_price' => 15, 'quantity' => 25, 'reorder_level' => 5],
            ['name' => 'Tyre 195/65R15 — Westlake', 'part_number' => 'TY-WL-023', 'category' => 'Tyres & Wheels', 'supplier' => 'Abossey Okai Wholesale', 'unit' => 'piece', 'cost_price' => 180, 'selling_price' => 280, 'quantity' => 8, 'reorder_level' => 4],
            ['name' => 'Tyre 205/55R16 — Maxxis', 'part_number' => 'TY-MX-024', 'category' => 'Tyres & Wheels', 'supplier' => 'Abossey Okai Wholesale', 'unit' => 'piece', 'cost_price' => 220, 'selling_price' => 350, 'quantity' => 1, 'reorder_level' => 3],
            ['name' => 'Fuel Filter — Universal', 'part_number' => 'FF-UNI-025', 'category' => 'Filters', 'supplier' => 'China Auto Parts', 'unit' => 'piece', 'cost_price' => 5, 'selling_price' => 10, 'quantity' => 2, 'reorder_level' => 10],
        ];

        foreach ($products as $data) {
            Product::firstOrCreate(
                ['name' => $data['name']],
                [
                    'name' => $data['name'],
                    'part_number' => $data['part_number'],
                    'category_id' => $categories[$data['category']] ?? 1,
                    'supplier_id' => $suppliers[$data['supplier']] ?? null,
                    'unit' => $data['unit'],
                    'cost_price' => $data['cost_price'],
                    'selling_price' => $data['selling_price'],
                    'quantity' => $data['quantity'],
                    'reorder_level' => $data['reorder_level'],
                    'is_active' => true,
                ]
            );
        }
    }
}
