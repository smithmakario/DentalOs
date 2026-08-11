<?php

namespace App\Policies;

use App\Enums\StaffPermission;
use App\Models\Staff;
use App\Models\TreatmentPlan;

class TreatmentPlanPolicy
{
    public function viewAny(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ViewPatientCharts);
    }

    public function view(Staff $staff, TreatmentPlan $treatmentPlan): bool
    {
        return $staff->hasPermission(StaffPermission::ViewPatientCharts);
    }

    public function create(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManageTreatments);
    }

    public function update(Staff $staff, TreatmentPlan $treatmentPlan): bool
    {
        return $staff->hasPermission(StaffPermission::ManageTreatments);
    }

    public function delete(Staff $staff, TreatmentPlan $treatmentPlan): bool
    {
        return $staff->hasPermission(StaffPermission::ManageTreatments);
    }
}
