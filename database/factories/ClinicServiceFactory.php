<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClinicServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('SRV-####'),
            'name' => $this->faker->words(3, true),
            'category' => 'Consultation',
            'description' => $this->faker->sentence(),
            'price' => 100.00,
            'duration_minutes' => 30,
            'is_active' => true,
        ];
    }
}
