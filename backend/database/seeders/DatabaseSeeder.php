<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CarerProfile;
use App\Models\ElderlyProfile;
use App\Models\FallEvent;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create admins
        User::factory()->state(['role' => 'admin'])->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create some carer profiles
        CarerProfile::factory(5)->create();

        // Create some elderly profiles
        ElderlyProfile::factory(20)->create();

        // Create fall events over last week
        FallEvent::factory(100)->create();
    }
}
