<?php

namespace Tests\Feature\Tenant;

use App\Models\Staff;
use App\Support\StaffRolePermissions;
use App\Enums\StaffPermission;
use App\Enums\StaffRole;
use Tests\TestCase;

class StaffBranchAccessTest extends TestCase
{
    public function test_staff_model_exposes_role_permissions(): void
    {
        $staff = new Staff([
            'role' => StaffRole::Dentist,
        ]);

        $this->assertTrue($staff->hasPermission(StaffPermission::ViewPatientCharts));
        $this->assertTrue($staff->hasPermission(StaffPermission::ManagePatients));
    }

    public function test_receptionist_has_patient_chart_access(): void
    {
        $permissions = StaffRolePermissions::forRole(StaffRole::Receptionist);

        $this->assertContains(StaffPermission::ViewPatientCharts, $permissions);
    }
}
