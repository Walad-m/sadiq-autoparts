<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Supplier;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'part_number' => strtoupper($this->faker->bothify('??-####')),
            'unit' => $this->faker->randomElement(['piece', 'set', 'pair', 'box']),
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'cost_price' => $this->faker->randomFloat(2, 10, 100),
            'selling_price' => $this->faker->randomFloat(2, 120, 300),
            'quantity' => $this->faker->numberBetween(10, 50),
            'reorder_level' => $this->faker->numberBetween(2, 10),
            'description' => $this->faker->sentence,
            'is_active' => true,
        ];
    }
}
