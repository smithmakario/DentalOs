<?php

namespace App\Policies;

use App\Enums\StaffPermission;
use App\Models\ClinicService;
use App\Models\Staff;

class ClinicServicePolicy
{
    public function viewAny(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBranchSettings)
            || $staff->hasPermission(StaffPermission::ManageTreatments);
    }

    public function view(Staff $staff, ClinicService $clinicService): bool
    {
        return $this->viewAny($staff);
    }

    public function create(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBranchSettings);
    }

    public function update(Staff $staff, ClinicService $clinicService): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBranchSettings);
    }

    public function delete(Staff $staff, ClinicService $clinicService): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBranchSettings);
    }
}
