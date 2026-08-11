<?php

namespace Tests\Feature\Tenant;

use App\Enums\StaffRole;
use App\Enums\TreatmentPlanStatus;
use App\Models\ClinicService;
use App\Models\Patient;
use App\Models\TreatmentPlan;
use Tests\TenantTestCase;

class ClinicServiceTest extends TenantTestCase
{
    public function test_clinic_admin_can_manage_services(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::ClinicAdmin]);

        $index = $this->actingAs($staff, 'staff')->get($this->tenantUrl('/clinic-services'));
        $index->assertOk();
        $index->assertSee(__('Clinic Services'));

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/clinic-services'), [
            'code' => 'D6010',
            'name' => 'Surgical Implant',
            'description' => 'Full implant placement with abutment.',
            'category' => 'Prosthodontics',
            'price' => 2500000,
            'duration_minutes' => 90,
            'icon' => 'dentistry',
            'is_recommended' => false,
            'is_active' => true,
        ]);

        $response->assertRedirect($this->tenantUrl('/clinic-services'));

        $this->tenant->run(function (): void {
            $this->assertDatabaseHas('clinic_services', [
                'code' => 'D6010',
                'name' => 'Surgical Implant',
                'duration_minutes' => 90,
                'price' => 2500000,
            ]);
        });
    }

    public function test_dentist_cannot_create_clinic_service(): void
    {
        $staff = $this->createStaff(['role' => StaffRole::Dentist]);

        $response = $this->actingAs($staff, 'staff')->post($this->tenantUrl('/clinic-services'), [
            'code' => 'D1110',
            'name' => 'Prophylaxis',
            'category' => 'Preventive',
            'price' => 120000,
            'duration_minutes' => 45,
        ]);

        $response->assertForbidden();
    }
}
