<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * The sponsored plans a company can put an employee on. Prices are per
     * seat per month in KES and are illustrative.
     */
    public function run(): void
    {
        $plans = [
            ['name' => 'Lite', 'monthly_credits' => 8, 'monthly_price' => 4000],
            ['name' => 'Standard', 'monthly_credits' => 16, 'monthly_price' => 7500],
            ['name' => 'Plus', 'monthly_credits' => 30, 'monthly_price' => 13000],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
