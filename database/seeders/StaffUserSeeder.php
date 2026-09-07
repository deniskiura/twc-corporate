<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class StaffUserSeeder extends Seeder
{
    /**
     * One TWC staff login for the internal console.
     */
    public function run(): void
    {
        User::factory()->staff()->create([
            'name' => 'TWC Staff',
            'email' => 'staff@twc.test',
            'api_token' => 'twc-staff-token',
        ]);
    }
}
