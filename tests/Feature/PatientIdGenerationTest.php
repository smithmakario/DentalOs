<?php

namespace Tests\Feature;

use App\Models\BranchProfile;
use App\Models\Patient;
use Tests\TenantTestCase;

class PatientIdGenerationTest extends TenantTestCase
{
    public function test_patient_id_is_auto_generated_on_creation(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            $this->assertNotNull($patient->patient_id_string);
            $this->assertMatchesRegularExpression('/^[A-Z]+-\d{4}-\d{5}$/', $patient->patient_id_string);
        });
    }

    public function test_patient_id_uses_branch_prefix(): void
    {
        $this->tenant->run(function (): void {
            BranchProfile::factory()->create(['branch_prefix' => 'WJ']);

            $patient = Patient::factory()->create();

            $this->assertStringStartsWith('WJ-', $patient->patient_id_string);
        });
    }

    public function test_patient_id_falls_back_to_tn_when_no_branch_profile(): void
    {
        $this->tenant->run(function (): void {
            BranchProfile::query()->delete();

            $patient = Patient::factory()->create();

            $this->assertStringStartsWith('TN-', $patient->patient_id_string);
        });
    }

    public function test_patient_id_increments_for_subsequent_patients(): void
    {
        $this->tenant->run(function (): void {
            BranchProfile::factory()->create(['branch_prefix' => 'LG']);

            $firstPatient = Patient::factory()->create();
            $secondPatient = Patient::factory()->create();
            $thirdPatient = Patient::factory()->create();

            $year = date('Y');
            $this->assertSame("LG-{$year}-00001", $firstPatient->patient_id_string);
            $this->assertSame("LG-{$year}-00002", $secondPatient->patient_id_string);
            $this->assertSame("LG-{$year}-00003", $thirdPatient->patient_id_string);
        });
    }

    public function test_patient_id_includes_current_year(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            $this->assertStringContainsString(date('Y'), $patient->patient_id_string);
        });
    }

    public function test_manually_set_patient_id_is_not_overwritten(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create(['patient_id_string' => 'CUSTOM-2026-99999']);

            $this->assertSame('CUSTOM-2026-99999', $patient->patient_id_string);
        });
    }
}
