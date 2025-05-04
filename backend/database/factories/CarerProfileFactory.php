<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CarerProfile;
use App\Models\User;
use Carbon\Carbon;

class CarerProfileFactory extends Factory
{
    protected $model = CarerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'carer']),
            'phone_number' => $this->faker->phoneNumber(),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'qualification' => $this->faker->randomElement(['Registered Nurse', 'Certified Nursing Assistant', 'Home Health Aide']),
            'specialization' => $this->faker->randomElement(['Geriatric Care', 'Physical Therapy', 'Occupational Therapy']),
            'years_of_experience' => $this->faker->numberBetween(1, 20),
            'availability_schedule' => [
                'monday' => '09:00-17:00',
                'tuesday' => '09:00-17:00',
                'wednesday' => '09:00-17:00',
                'thursday' => '09:00-17:00',
                'friday' => '09:00-17:00',
            ],
            'max_elderly_capacity' => $this->faker->numberBetween(1, 5),
            'current_elderly_count' => 0,
            'status' => $this->faker->randomElement(['active', 'inactive', 'on_leave']),
            'last_active_at' => Carbon::now()->subDays($this->faker->numberBetween(0, 10)),
            'notes' => $this->faker->sentence(),
        ];
    }
} 