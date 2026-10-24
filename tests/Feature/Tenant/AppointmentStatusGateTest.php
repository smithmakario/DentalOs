<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\StaffRole;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Staff;
use Tests\TenantTestCase;

class AppointmentStatusGateTest extends TenantTestCase
{
    public function test_day_of_status_is_blocked_before_appointment_date_without_emergency(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $appointmentId = $this->tenant->run(function (): int {
            $patient = Patient::factory()->create();
            $dentist = Staff::factory()->create(['role' => StaffRole::Dentist]);

            return Appointment::create([
                'patient_id' => $patient->id,
                'provider_id' => $dentist->id,
                'title' => 'Future consult',
                'scheduled_at' => now()->addDays(3)->setTime(10, 0),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::Scheduled,
            ])->id;
        });

        $this->actingAs($staff, 'staff')
            ->from($this->tenantUrl("/appointments/{$appointmentId}"))
            ->patch($this->tenantUrl("/appointments/{$appointmentId}/status"), [
                'status' => AppointmentStatus::CheckedIn->value,
            ])
            ->assertRedirect($this->tenantUrl("/appointments/{$appointmentId}"))
            ->assertSessionHasErrors('status');

        $this->tenant->run(function () use ($appointmentId): void {
            $this->assertSame(
                AppointmentStatus::Scheduled,
                Appointment::query()->findOrFail($appointmentId)->status,
            );
        });
    }

    public function test_emergency_allows_day_of_status_before_appointment_date(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $appointmentId = $this->tenant->run(function (): int {
            $patient = Patient::factory()->create();
            $dentist = Staff::factory()->create(['role' => StaffRole::Dentist]);

            return Appointment::create([
                'patient_id' => $patient->id,
                'provider_id' => $dentist->id,
                'title' => 'Emergency consult',
                'scheduled_at' => now()->addDays(2)->setTime(14, 0),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::Scheduled,
            ])->id;
        });

        $this->actingAs($staff, 'staff')
            ->patch($this->tenantUrl("/appointments/{$appointmentId}/status"), [
                'status' => AppointmentStatus::CheckedIn->value,
                'is_emergency' => true,
            ])
            ->assertRedirect($this->tenantUrl("/appointments/{$appointmentId}"))
            ->assertSessionHasNoErrors();

        $this->tenant->run(function () use ($appointmentId): void {
            $appointment = Appointment::query()->findOrFail($appointmentId);
            $this->assertSame(AppointmentStatus::CheckedIn, $appointment->status);
            $this->assertNotNull($appointment->checked_in_at);
        });
    }

    public function test_confirm_and_cancel_remain_available_before_appointment_day(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $appointmentId = $this->tenant->run(function (): int {
            $patient = Patient::factory()->create();
            $dentist = Staff::factory()->create(['role' => StaffRole::Dentist]);

            return Appointment::create([
                'patient_id' => $patient->id,
                'provider_id' => $dentist->id,
                'title' => 'Booked visit',
                'scheduled_at' => now()->addWeek()->setTime(9, 0),
                'duration_minutes' => 30,
                'status' => AppointmentStatus::Scheduled,
            ])->id;
        });

        $this->actingAs($staff, 'staff')
            ->patch($this->tenantUrl("/appointments/{$appointmentId}/status"), [
                'status' => AppointmentStatus::Confirmed->value,
            ])
            ->assertRedirect($this->tenantUrl("/appointments/{$appointmentId}"))
            ->assertSessionHasNoErrors();

        $this->tenant->run(function () use ($appointmentId): void {
            $this->assertSame(
                AppointmentStatus::Confirmed,
                Appointment::query()->findOrFail($appointmentId)->status,
            );
        });
    }

    public function test_invoice_can_be_prefills_from_appointment(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);

        $appointmentId = $this->tenant->run(function (): int {
            $patient = Patient::factory()->create();
            $dentist = Staff::factory()->create(['role' => StaffRole::Dentist]);

            return Appointment::create([
                'patient_id' => $patient->id,
                'provider_id' => $dentist->id,
                'title' => 'Scaling',
                'scheduled_at' => now()->setTime(11, 0),
                'duration_minutes' => 45,
                'status' => AppointmentStatus::Completed,
                'completed_at' => now(),
            ])->id;
        });

        $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl("/invoices/create?appointment_id={$appointmentId}"))
            ->assertOk()
            ->assertSee('Scaling')
            ->assertSee('name="appointment_id"', false);

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/invoices'), [
            'patient_id' => $this->tenant->run(
                fn (): int => (int) Appointment::query()->findOrFail($appointmentId)->patient_id,
            ),
            'appointment_id' => $appointmentId,
            'status' => InvoiceStatus::Sent->value,
            'items' => [
                [
                    'description' => 'Scaling',
                    'quantity' => 1,
                    'unit_price' => 120,
                ],
            ],
        ]);

        $invoiceId = $this->tenant->run(function () use ($appointmentId): int {
            return (int) Invoice::query()->where('appointment_id', $appointmentId)->value('id');
        });

        $response->assertRedirect($this->tenantUrl("/invoices/{$invoiceId}"));

        $this->tenant->run(function () use ($appointmentId, $invoiceId): void {
            $invoice = Invoice::query()->findOrFail($invoiceId);
            $this->assertSame($appointmentId, $invoice->appointment_id);
            $this->assertSame('120.00', $invoice->total);
            $this->assertSame(InvoiceStatus::Sent, $invoice->status);
        });
    }
}
