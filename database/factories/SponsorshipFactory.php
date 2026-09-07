<?php

namespace Database\Factories;

use App\Enums\SponsorshipStatus;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Sponsorship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sponsorship>
 */
class SponsorshipFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'plan_id' => Plan::factory(),
            'invited_by_id' => User::factory(),
            'email' => fake()->unique()->safeEmail(),
            'invite_token' => Sponsorship::generateToken(),
            'status' => SponsorshipStatus::Invited,
            'invited_at' => now(),
            'last_sent_at' => now(),
        ];
    }
}
