<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'title' => $this->faker->words(3, true),
            'category' => $this->faker->randomElement(['rent', 'utilities', 'transport', 'salaries', 'stock', 'maintenance', 'other']),
            'amount' => $this->faker->randomFloat(2, 50, 1000),
            'payment_method' => $this->faker->randomElement(['cash', 'momo']),
            'expense_date' => clone $this->faker->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->sentence,
        ];
    }
}
