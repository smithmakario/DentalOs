<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Enums\StaffRole;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TenantTestCase;

class AppointmentReminderTest extends TenantTestCase
{
    public function test_command_sends_24h_and_1h_reminders_once(): void
    {
        Notification::fake();

        $this->tenant->run(function (): void {
            $dentist = Staff::factory()->create(['role' => StaffRole::Dentist]);

            Appointment::query()->create([
                'patient_id' => Patient::factory()->create([
                    'email' => 'patient24h@example.com',
                ])->id,
                'provider_id' => $dentist->id,
                'title' => 'Tomorrow visit',
                'scheduled_at' => now()->addHours(24),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::Confirmed,
            ]);

            Appointment::query()->create([
                'patient_id' => Patient::factory()->create([
                    'email' => 'patient1h@example.com',
                ])->id,
                'provider_id' => $dentist->id,
                'title' => 'Soon visit',
                'scheduled_at' => now()->addHour(),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::Confirmed,
            ]);
        });

        $this->artisan('appointments:send-reminders', ['--tenant' => $this->tenant->id])
            ->assertSuccessful();

        Notification::assertSentOnDemandTimes(AppointmentReminderNotification::class, 2);

        $this->tenant->run(function (): void {
            $this->assertSame(1, Appointment::query()->whereNotNull('reminder_24h_sent_at')->count());
            $this->assertSame(1, Appointment::query()->whereNotNull('reminder_1h_sent_at')->count());
        });

        Notification::fake();

        $this->artisan('appointments:send-reminders', ['--tenant' => $this->tenant->id])
            ->assertSuccessful();

        Notification::assertNothingSent();
    }
}
