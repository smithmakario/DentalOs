<?php

namespace Tests\Feature\Tenant;

use App\Enums\StaffRole;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\PatientVital;
use Tests\TenantTestCase;

class PatientEmrTest extends TenantTestCase
{
    public function test_staff_can_search_patients_by_patient_id_string(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);

        $this->tenant->run(function (): void {
            Patient::factory()->create([
                'first_name' => 'Ada',
                'last_name' => 'Okafor',
                'patient_id_string' => 'DTN-00042',
            ]);
            Patient::factory()->create([
                'first_name' => 'Other',
                'last_name' => 'Patient',
                'patient_id_string' => 'DTN-00999',
            ]);
        });

        $response = $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl('/patients?search=DTN-00042'));

        $response->assertOk();
        $response->assertSee('Ada');
        $response->assertSee('DTN-00042');
        $response->assertDontSee('Other');
    }

    public function test_staff_can_add_allergy_and_vitals_to_patient_chart(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Receptionist]);
        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl("/patients/{$patient->id}/allergies"), [
                'allergen' => 'Penicillin',
                'reaction' => 'Rash',
                'severity' => 'severe',
                'status' => 'active',
            ])
            ->assertRedirect($this->tenantUrl("/patients/{$patient->id}"));

        $this->actingAs($staff, 'staff')
            ->post($this->tenantUrl("/patients/{$patient->id}/vitals"), [
                'blood_pressure' => '120/80',
                'heart_rate' => 72,
                'temperature' => 36.8,
                'weight' => 70.5,
            ])
            ->assertRedirect($this->tenantUrl("/patients/{$patient->id}"));

        $this->tenant->run(function () use ($patient): void {
            $this->assertTrue(
                PatientAllergy::query()
                    ->where('patient_id', $patient->id)
                    ->where('allergen', 'Penicillin')
                    ->exists()
            );

            $this->assertTrue(
                PatientVital::query()
                    ->where('patient_id', $patient->id)
                    ->where('blood_pressure', '120/80')
                    ->exists()
            );
        });

        $this->actingAs($staff, 'staff')
            ->get($this->tenantUrl("/patients/{$patient->id}"))
            ->assertOk()
            ->assertSee('Penicillin')
            ->assertSee('120/80');
    }
}
