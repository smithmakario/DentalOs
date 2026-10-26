<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'provider_id' => Staff::factory(),
            'scheduled_at' => now()->addDay()->setTime(10, 0),
            'duration_minutes' => 30,
            'status' => AppointmentStatus::Scheduled,
            'title' => 'Routine Checkup',
        ];
    }
}
