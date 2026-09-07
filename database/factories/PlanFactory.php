<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'monthly_credits' => fake()->numberBetween(8, 30),
            'monthly_price' => fake()->numberBetween(4000, 13000),
            'currency' => 'KES',
            'is_active' => true,
        ];
    }
}
