<?php

namespace App\Policies;

use App\Enums\StaffPermission;
use App\Models\Patient;
use App\Models\Staff;

class PatientPolicy
{
    public function viewAny(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ViewPatientCharts);
    }

    public function view(Staff $staff, Patient $patient): bool
    {
        return $staff->hasPermission(StaffPermission::ViewPatientCharts);
    }

    public function create(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManagePatients);
    }

    public function update(Staff $staff, Patient $patient): bool
    {
        return $staff->hasPermission(StaffPermission::ManagePatients);
    }

    public function delete(Staff $staff, Patient $patient): bool
    {
        return $staff->hasPermission(StaffPermission::ManagePatients);
    }
}
