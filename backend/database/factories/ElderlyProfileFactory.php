<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ElderlyProfile;
use App\Models\User;
use Carbon\Carbon;

class ElderlyProfileFactory extends Factory
{
    protected $model = ElderlyProfile::class;

    public function definition(): array
    {
        // Generate birth between 65 and 90 years ago
        $dob = $this->faker->dateTimeBetween('-90 years', '-65 years');
        $gender = $this->faker->randomElement(['male', 'female', 'other']);
        return [
            'user_id' => User::factory()->state(['role' => 'elderly']),
            'date_of_birth' => $dob->format('Y-m-d'),
            'gender' => $gender,
            'profile_photo' => null,
            'height' => $this->faker->randomFloat(2, 140, 190),
            'weight' => $this->faker->randomFloat(2, 50, 90),
            'blood_type' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'national_id' => strtoupper($this->faker->bothify('??#######')), // e.g. AB1234567
            'primary_phone' => $this->faker->phoneNumber(),
            'secondary_phone' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'current_address' => $this->faker->address(),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'emergency_contact_relationship' => $this->faker->randomElement(['Child', 'Spouse', 'Friend', 'Sibling']),
            'medical_conditions' => $this->faker->optional()->sentence(),
            'allergies' => $this->faker->optional()->word(),
            'current_medications' => $this->faker->optional()->word(),
            'disabilities' => $this->faker->optional()->sentence(),
            'mobility_status' => $this->faker->randomElement(['independent', 'needs_assistance', 'wheelchair_bound']),
            'vision_status' => $this->faker->randomElement(['normal', 'glasses', 'impaired']),
            'hearing_status' => $this->faker->randomElement(['normal', 'hearing_aid', 'impaired']),
            'last_medical_checkup' => $this->faker->optional()->dateTimeBetween('-1 year', 'now')?->format('Y-m-d'),
            'care_level' => $this->faker->randomElement(['basic', 'moderate', 'intensive']),
            'special_care_instructions' => $this->faker->optional()->sentence(),
            'daily_routine_notes' => $this->faker->optional()->sentence(),
            'dietary_restrictions' => $this->faker->optional()->sentence(),
            'preferred_language' => $this->faker->randomElement(['English', 'Spanish', 'French', 'German']),
            'device_id' => $this->faker->optional()->bothify('DEV##??##'),
            'device_status' => $this->faker->randomElement(['active', 'inactive']),
            'last_device_check' => $this->faker->optional()->dateTimeBetween('-1 month', 'now')?->format('Y-m-d'),
            'device_battery_level' => $this->faker->optional()->numberBetween(0, 100),
            'device_location' => $this->faker->optional()->city(),
            'preferred_hospital' => $this->faker->optional()->company() . ' Hospital',
            'insurance_information' => $this->faker->optional()->sentence(),
            'living_situation' => $this->faker->randomElement(['lives_alone', 'with_family', 'assisted_living']),
            'activity_level' => $this->faker->randomElement(['active', 'moderate', 'sedentary']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
} 