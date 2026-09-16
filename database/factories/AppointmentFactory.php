<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'provider_id' => Staff::factory(),
            'scheduled_at' => now()->addDays(1)->setTime(10, 0),
            'duration_minutes' => 30,
            'status' => AppointmentStatus::Scheduled,
            'title' => 'Routine Checkup',
        ];
    }
}
