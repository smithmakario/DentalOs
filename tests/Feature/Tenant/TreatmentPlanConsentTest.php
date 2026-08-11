<?php

namespace Tests\Feature\Tenant;

use App\Enums\StaffRole;
use App\Enums\TreatmentPlanStatus;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanOption;
use Illuminate\Support\Facades\Storage;
use Tests\TenantTestCase;

class TreatmentPlanConsentTest extends TenantTestCase
{
    public function test_dentist_can_record_digital_consent_for_treatment_option(): void
    {
        Storage::fake('local');

        $staff = $this->createStaff(['role' => StaffRole::Dentist]);

        [$plan, $option] = $this->tenant->run(function () use ($staff): array {
            $patient = Patient::factory()->create();

            $plan = TreatmentPlan::query()->create([
                'patient_id' => $patient->id,
                'provider_id' => $staff->id,
                'title' => 'Tooth #19 Restoration',
                'status' => TreatmentPlanStatus::Draft,
                'estimated_total' => 3500,
            ]);

            $optionA = TreatmentPlanOption::query()->create([
                'treatment_plan_id' => $plan->id,
                'name' => 'Option A: Dental Implant',
                'estimated_total' => 3500,
                'sort_order' => 0,
            ]);

            $optionA->items()->create([
                'name' => 'Implant Placement',
                'procedure_code' => 'D6010',
                'phase_name' => 'Phase 1',
                'estimated_cost' => 3500,
            ]);

            TreatmentPlanOption::query()->create([
                'treatment_plan_id' => $plan->id,
                'name' => 'Option B: Dental Bridge',
                'estimated_total' => 2200,
                'sort_order' => 1,
            ]);

            return [$plan, $optionA];
        });

        $signature = 'data:image/png;base64,'.base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

        $response = $this->actingAs($staff, 'staff')->post(
            $this->tenantUrl('/treatment-plans/'.$plan->id.'/options/'.$option->id.'/consent'),
            [
                'consent_signer_name' => 'Jane Patient',
                'consent_statement' => 'I consent to Option A: Dental Implant.',
                'consent_signature' => $signature,
                'consent_acknowledged' => '1',
            ],
        );

        $response->assertRedirect($this->tenantUrl('/treatment-plans/'.$plan->id));

        $this->tenant->run(function () use ($option): void {
            $option->refresh();

            $this->assertTrue($option->hasConsent());
            $this->assertTrue($option->is_selected);
            $this->assertSame('Jane Patient', $option->consent_signer_name);
            Storage::disk('local')->assertExists($option->consent_signature_path);
        });
    }
}
