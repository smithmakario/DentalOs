<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Enums\StaffRole;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use Tests\TenantTestCase;

class AppointmentTest extends TenantTestCase
{
    public function test_receptionist_can_view_appointments_index(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/appointments'))
            ->assertOk()
            ->assertViewIs('tenant.appointments.index');
    }

    public function test_receptionist_can_schedule_an_appointment(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);
        $dentist = $this->createStaff(['role' => StaffRole::Dentist]);
        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $scheduledAt = now()->addDays(1)->setHour(10)->setMinute(0)->setSecond(0);

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/appointments'), [
            'patient_id' => $patient->id,
            'provider_id' => $dentist->id,
            'title' => 'Routine Checkup',
            'scheduled_at' => $scheduledAt->toDateTimeString(),
            'duration_minutes' => 30,
            'status' => AppointmentStatus::Scheduled->value,
            'notes' => 'First visit',
        ]);

        $appointmentId = $this->tenant->run(function () use ($patient): int {
            return (int) Appointment::query()->where('patient_id', $patient->id)->value('id');
        });

        $response->assertRedirect($this->tenantUrl("/appointments/{$appointmentId}"));

        $this->tenant->run(function () use ($patient, $dentist): void {
            $appointment = Appointment::query()->where('patient_id', $patient->id)->first();
            $this->assertNotNull($appointment);
            $this->assertSame($dentist->id, $appointment->provider_id);
            $this->assertSame('Routine Checkup', $appointment->title);
            $this->assertSame(AppointmentStatus::Scheduled, $appointment->status);
        });
    }

    public function test_appointment_status_can_be_updated_to_checked_in(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $appointmentId = $this->tenant->run(function (): int {
            $patient = Patient::factory()->create();
            $dentist = Staff::factory()->create(['role' => StaffRole::Dentist]);

            return Appointment::create([
                'patient_id' => $patient->id,
                'provider_id' => $dentist->id,
                'title' => 'Consultation',
                'scheduled_at' => now()->addHour(),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::Scheduled,
            ])->id;
        });

        $response = $this->actingAs($staff, 'staff')->patch($this->tenantUrl("/appointments/{$appointmentId}/status"), [
            'status' => AppointmentStatus::CheckedIn->value,
        ]);

        $response->assertRedirect($this->tenantUrl("/appointments/{$appointmentId}"));

        $this->tenant->run(function () use ($appointmentId): void {
            $appointment = Appointment::query()->findOrFail($appointmentId);
            $this->assertSame(AppointmentStatus::CheckedIn, $appointment->status);
            $this->assertNotNull($appointment->checked_in_at);
        });
    }

    public function test_dentist_can_complete_appointment(): void
    {
        $dentist = $this->createStaff(['role' => StaffRole::Dentist]);

        $appointmentId = $this->tenant->run(function () use ($dentist): int {
            $patient = Patient::factory()->create();

            return Appointment::create([
                'patient_id' => $patient->id,
                'provider_id' => $dentist->id,
                'title' => 'Treatment',
                'scheduled_at' => now()->subHour(),
                'duration_minutes' => 45,
                'status' => AppointmentStatus::CheckedIn,
                'checked_in_at' => now()->subMinutes(50),
            ])->id;
        });

        $response = $this->actingAs($dentist, 'staff')->patch($this->tenantUrl("/appointments/{$appointmentId}/status"), [
            'status' => AppointmentStatus::Completed->value,
        ]);

        $response->assertRedirect($this->tenantUrl("/appointments/{$appointmentId}"));

        $this->tenant->run(function () use ($appointmentId): void {
            $appointment = Appointment::query()->findOrFail($appointmentId);
            $this->assertSame(AppointmentStatus::Completed, $appointment->status);
            $this->assertNotNull($appointment->completed_at);
        });
    }

    public function test_receptionist_can_delete_appointment(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $appointmentId = $this->tenant->run(function (): int {
            $patient = Patient::factory()->create();
            $dentist = Staff::factory()->create(['role' => StaffRole::Dentist]);

            return Appointment::create([
                'patient_id' => $patient->id,
                'provider_id' => $dentist->id,
                'title' => 'To be deleted',
                'scheduled_at' => now()->addDays(2),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::Scheduled,
            ])->id;
        });

        $response = $this->actingAs($staff, 'staff')->delete($this->tenantUrl("/appointments/{$appointmentId}"));

        $response->assertRedirect($this->tenantUrl('/appointments'));

        $this->tenant->run(function () use ($appointmentId): void {
            $this->assertNull(Appointment::query()->find($appointmentId));
        });
    }
}
