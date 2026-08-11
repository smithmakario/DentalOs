<?php

namespace App\Policies;

use App\Enums\StaffPermission;
use App\Models\Staff;
use App\Models\TreatmentPlanOption;

class TreatmentPlanOptionPolicy
{
    public function signConsent(Staff $staff, TreatmentPlanOption $option): bool
    {
        return $staff->hasPermission(StaffPermission::ManageTreatments);
    }
}
