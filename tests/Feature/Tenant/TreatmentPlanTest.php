<?php

namespace Tests\Feature\Tenant;

use App\Enums\StaffRole;
use App\Enums\TreatmentPlanStatus;
use App\Models\ClinicService;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use Tests\TenantTestCase;

class TreatmentPlanTest extends TenantTestCase
{
    public function test_dentist_can_view_treatment_plans_index(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Dentist]);

        $response = $this->actingAs($staff, 'staff')->get($this->tenantUrl('/treatment-plans'));

        $response->assertOk();
        $response->assertSee(__('Treatment Plans'));
    }

    public function test_dentist_can_create_multi_option_treatment_plan(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Dentist]);

        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $service = $this->tenant->run(fn (): ClinicService => ClinicService::query()->create([
            'code' => 'D6010',
            'name' => 'Implant',
            'category' => 'Prosthodontics',
            'price' => 3500,
        ]));

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/treatment-plans'), [
            'patient_id' => $patient->id,
            'provider_id' => $staff->id,
            'title' => 'Tooth #19 Restoration',
            'description' => 'Missing lower right first molar',
            'status' => TreatmentPlanStatus::Draft->value,
            'options' => [
                [
                    'name' => 'Option A: Dental Implant',
                    'description' => 'Preferred long-term solution',
                    'items' => [
                        [
                            'clinic_service_id' => $service->id,
                            'name' => 'Implant Placement',
                            'procedure_code' => 'D6010',
                            'phase_name' => 'Phase 1: Surgery',
                            'estimated_cost' => 3500,
                        ],
                    ],
                ],
                [
                    'name' => 'Option B: Dental Bridge',
                    'items' => [
                        [
                            'name' => 'Fixed Bridge',
                            'procedure_code' => 'D6240',
                            'phase_name' => 'Phase 1: Preparation',
                            'estimated_cost' => 2200,
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->tenant->run(function () use ($patient): void {
            $plan = TreatmentPlan::query()->where('patient_id', $patient->id)->firstOrFail();

            $this->assertSame('Tooth #19 Restoration', $plan->title);
            $this->assertCount(2, $plan->options);
            $this->assertEquals(2200, (float) $plan->estimated_total);
            $this->assertEquals(3500, (float) $plan->options->first()->estimated_total);
        });
    }

    public function test_hygienist_cannot_create_treatment_plan(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Hygienist]);

        $patient = $this->tenant->run(fn (): Patient => Patient::factory()->create());

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/treatment-plans'), [
            'patient_id' => $patient->id,
            'provider_id' => $staff->id,
            'title' => 'Cleaning Plan',
            'status' => TreatmentPlanStatus::Draft->value,
            'options' => [
                [
                    'name' => 'Option A',
                    'items' => [
                        ['name' => 'Prophylaxis', 'estimated_cost' => 120],
                    ],
                ],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_create_shows_validation_errors_when_required_fields_are_missing(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Dentist]);

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/treatment-plans'), [
            'title' => 'Incomplete Plan',
            'status' => TreatmentPlanStatus::Draft->value,
            'options' => [
                [
                    'name' => 'Option A',
                    'items' => [
                        ['name' => ''],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect($this->tenantUrl('/treatment-plans/create'));
        $response->assertSessionHasErrors(['patient_id', 'provider_id', 'options.0.items.0.name']);

        $response = $this->actingAs($staff, 'staff')->get($this->tenantUrl('/treatment-plans/create'));
        $response->assertOk();
        $response->assertSee(__('Please fix the following errors:'));
    }
}
