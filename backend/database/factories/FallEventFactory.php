<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FallEvent;
use App\Models\ElderlyProfile;
use App\Models\User;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class FallEventFactory extends Factory
{
    protected $model = FallEvent::class;

    public function definition(): array
    {
        // Pick a random elderly profile
        $elderly = ElderlyProfile::inRandomOrder()->first() ?? ElderlyProfile::factory();
        $detectedAt = $this->faker->dateTimeBetween('-7 days', 'now');
        // Determine status and related fields
        $status = $this->faker->randomElement(['detected', 'confirmed', 'false_alarm', 'resolved']);
        $resolvedBy = null;
        $resolvedAt = null;
        $responseTime = null;
        if (in_array($status, ['confirmed', 'false_alarm', 'resolved'])) {
            // Pick a carer user
            $carer = User::where('role', 'carer')->inRandomOrder()->first() ?? User::factory()->state(['role' => 'carer'])->create();
            $resolvedBy = $carer->id;
            // resolvedAt between detectedAt and now
            $resolvedAt = $this->faker->dateTimeBetween($detectedAt, 'now');
            $responseTime = $resolvedAt->getTimestamp() - $detectedAt->getTimestamp();
        }

        return [
            'elderly_id' => $elderly instanceof ElderlyProfile ? $elderly->id : $elderly->create()->id,
            'detected_at' => $detectedAt,
            'confidence_score' => $this->faker->randomFloat(2, 50, 100),
            'detection_method' => $this->faker->randomElement(['sensor', 'manual', 'ai']),
            'location' => [
                'lat' => $this->faker->latitude(),
                'lng' => $this->faker->longitude(),
            ],
            'location_description' => $this->faker->randomElement(['Living Room', 'Kitchen', 'Bedroom', 'Bathroom']),
            'sensor_data' => [
                'acc_x' => $this->faker->randomFloat(3, -2, 2),
                'acc_y' => $this->faker->randomFloat(3, -2, 2),
                'acc_z' => $this->faker->randomFloat(3, -2, 2),
            ],
            'status' => $status,
            'severity_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'notes' => $this->faker->optional()->sentence(),
            'medical_notes' => $this->faker->optional()->sentence(),
            'required_medical_attention' => $this->faker->boolean(30),
            'resolved_by' => $resolvedBy,
            'resolved_at' => $resolvedAt,
            'response_time_seconds' => $responseTime,
            'response_actions' => $this->faker->optional()->randomElements(['Called Carer', 'Sent SMS', 'Notified Family'], 2),
        ];
    }
} 