<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\PatientLabResult;
use App\Models\PatientVital;
use Tests\TenantTestCase;

class EmrModuleTest extends TenantTestCase
{
    // ──────────────────────────────────────────────────────────
    // Allergies
    // ──────────────────────────────────────────────────────────

    public function test_allergy_can_be_added_to_patient(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            $allergy = PatientAllergy::create([
                'patient_id' => $patient->id,
                'allergen' => 'Penicillin',
                'reaction' => 'Rash and swelling',
                'severity' => 'high',
                'status' => 'active',
            ]);

            $this->assertDatabaseHas('patient_allergies', [
                'patient_id' => $patient->id,
                'allergen' => 'Penicillin',
            ]);

            $this->assertNotNull($allergy->id);
        });
    }

    public function test_patient_has_many_allergies(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            PatientAllergy::create(['patient_id' => $patient->id, 'allergen' => 'Latex', 'severity' => 'medium', 'status' => 'active']);
            PatientAllergy::create(['patient_id' => $patient->id, 'allergen' => 'Aspirin', 'severity' => 'low', 'status' => 'active']);

            $this->assertCount(2, $patient->allergies);
        });
    }

    public function test_allergy_belongs_to_patient(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            $allergy = PatientAllergy::create([
                'patient_id' => $patient->id,
                'allergen' => 'Sulfa',
                'severity' => 'high',
                'status' => 'active',
            ]);

            $this->assertSame($patient->id, $allergy->patient->id);
        });
    }

    // ──────────────────────────────────────────────────────────
    // Vitals
    // ──────────────────────────────────────────────────────────

    public function test_vital_signs_can_be_recorded_for_patient(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            $vital = PatientVital::create([
                'patient_id' => $patient->id,
                'recorded_at' => now(),
                'blood_pressure' => '120/80',
                'heart_rate' => 72,
                'temperature' => 36.60,
                'weight' => 70.50,
            ]);

            $this->assertDatabaseHas('patient_vitals', [
                'patient_id' => $patient->id,
                'blood_pressure' => '120/80',
            ]);

            $this->assertNotNull($vital->id);
        });
    }

    public function test_patient_has_many_vitals(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            PatientVital::create(['patient_id' => $patient->id, 'recorded_at' => now()->subDays(7), 'blood_pressure' => '125/82']);
            PatientVital::create(['patient_id' => $patient->id, 'recorded_at' => now(), 'blood_pressure' => '120/80']);

            $this->assertCount(2, $patient->vitals);
        });
    }

    // ──────────────────────────────────────────────────────────
    // Lab Results
    // ──────────────────────────────────────────────────────────

    public function test_lab_result_can_be_recorded_for_patient(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            $result = PatientLabResult::create([
                'patient_id' => $patient->id,
                'test_name' => 'Full Blood Count',
                'test_date' => today()->toDateString(),
                'result' => 'Haemoglobin: 13.5 g/dL',
                'reference_range' => '13.0-17.0 g/dL',
            ]);

            $this->assertDatabaseHas('patient_lab_results', [
                'patient_id' => $patient->id,
                'test_name' => 'Full Blood Count',
            ]);

            $this->assertNotNull($result->id);
        });
    }

    public function test_patient_has_many_lab_results(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            PatientLabResult::create(['patient_id' => $patient->id, 'test_name' => 'FBC', 'test_date' => today()->toDateString(), 'result' => 'Normal']);
            PatientLabResult::create(['patient_id' => $patient->id, 'test_name' => 'HbA1c', 'test_date' => today()->toDateString(), 'result' => '5.4%']);

            $this->assertCount(2, $patient->labResults);
        });
    }

    public function test_lab_result_belongs_to_patient(): void
    {
        $this->tenant->run(function (): void {
            $patient = Patient::factory()->create();

            $result = PatientLabResult::create([
                'patient_id' => $patient->id,
                'test_name' => 'Urine Analysis',
                'test_date' => today()->toDateString(),
                'result' => 'Clear',
            ]);

            $this->assertSame($patient->id, $result->patient->id);
        });
    }
}
