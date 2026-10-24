<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Enums\StaffRole;
use App\Models\Appointment;
use App\Models\Patient;
use Tests\TenantTestCase;

class AppointmentBoardTest extends TenantTestCase
{
    public function test_receptionist_can_view_day_board(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);
        $dentist = $this->createStaff(['role' => StaffRole::Dentist, 'email' => 'board-dentist@example.com']);

        $this->tenant->run(function () use ($dentist): void {
            $patient = Patient::factory()->create(['first_name' => 'Board', 'last_name' => 'Patient']);

            Appointment::query()->create([
                'patient_id' => $patient->id,
                'provider_id' => $dentist->id,
                'title' => 'Cleaning',
                'scheduled_at' => now()->setTime(10, 0),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::Confirmed,
            ]);
        });

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/appointments?view=board&date='.now()->format('Y-m-d')));

        $response->assertOk()
            ->assertViewIs('tenant.appointments.board')
            ->assertSee('Day Board')
            ->assertSee('Board Patient')
            ->assertSee('Cleaning');
    }

    public function test_dentist_can_save_soap_clinical_notes(): void
    {
        $dentist = $this->createStaff(['role' => StaffRole::Dentist]);

        $appointmentId = $this->tenant->run(function () use ($dentist): int {
            $patient = Patient::factory()->create();

            return Appointment::query()->create([
                'patient_id' => $patient->id,
                'provider_id' => $dentist->id,
                'title' => 'Exam',
                'scheduled_at' => now()->addHour(),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::InProgress,
            ])->id;
        });

        $response = $this->actingAs($dentist, 'staff')->patch(
            $this->tenantUrl("/appointments/{$appointmentId}/clinical-notes"),
            [
                'soap_subjective' => 'Patient reports tooth pain',
                'soap_objective' => 'Caries on #16',
                'soap_assessment' => 'Deep caries',
                'soap_plan' => 'Composite restoration',
            ],
        );

        $response->assertRedirect($this->tenantUrl("/appointments/{$appointmentId}"));

        $this->tenant->run(function () use ($appointmentId): void {
            $appointment = Appointment::query()->findOrFail($appointmentId);
            $this->assertSame('Patient reports tooth pain', $appointment->soap_subjective);
            $this->assertSame('Composite restoration', $appointment->soap_plan);
            $this->assertTrue($appointment->hasSoapNotes());
        });
    }
}
