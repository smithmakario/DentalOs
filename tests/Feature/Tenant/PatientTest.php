<?php

namespace Tests\Feature\Tenant;

use App\Enums\PaymentMethod;
use App\Enums\StaffRole;
use App\Models\Patient;
use Tests\TenantTestCase;

class PatientTest extends TenantTestCase
{
    public function test_staff_can_view_patients_index(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $this->tenant->run(function (): void {
            Patient::factory()->count(3)->create();
        });

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/patients'));

        $response->assertOk()
            ->assertViewIs('tenant.patients.index')
            ->assertViewHas('patients')
            ->assertViewHas('totalPatients', 3);
    }

    public function test_staff_can_view_patient_details(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);
        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl("/patients/{$patient->id}"));

        $response->assertOk()
            ->assertViewIs('tenant.patients.show')
            ->assertViewHas('patient');
    }

    public function test_staff_can_view_patient_edit_page(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);
        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl("/patients/{$patient->id}/edit"));

        $response->assertOk()
            ->assertViewIs('tenant.patients.edit')
            ->assertViewHas('patient');
    }

    public function test_staff_can_update_patient_details(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);
        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $response = $this->actingAs($staff, 'staff')
            ->patch($this->tenantUrl("/patients/{$patient->id}"), [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'phone' => '08011122233',
                'preferred_payment_method' => PaymentMethod::Cash->value,
                'is_active' => true,
            ]);

        $response->assertRedirect($this->tenantUrl("/patients/{$patient->id}"));

        $this->tenant->run(function () use ($patient): void {
            $updatedPatient = Patient::query()->findOrFail($patient->id);
            $this->assertSame('Updated', $updatedPatient->first_name);
            $this->assertSame('Name', $updatedPatient->last_name);
            $this->assertSame('08011122233', $updatedPatient->phone);
            $this->assertSame(PaymentMethod::Cash, $updatedPatient->preferred_payment_method);
        });
    }

    public function test_staff_can_delete_patient(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);
        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $response = $this->actingAs($staff, 'staff')
            ->delete($this->tenantUrl("/patients/{$patient->id}"));

        $response->assertRedirect($this->tenantUrl('/patients'));

        $this->tenant->run(function () use ($patient): void {
            $this->assertNull(Patient::query()->find($patient->id));
        });
    }
}
