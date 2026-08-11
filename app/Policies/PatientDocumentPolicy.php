<?php

namespace App\Policies;

use App\Enums\StaffPermission;
use App\Models\PatientDocument;
use App\Models\Staff;

class PatientDocumentPolicy
{
    public function viewAny(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ViewPatientCharts);
    }

    public function view(Staff $staff, PatientDocument $patientDocument): bool
    {
        return $staff->hasPermission(StaffPermission::ViewPatientCharts);
    }

    public function create(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManagePatients);
    }

    public function delete(Staff $staff, PatientDocument $patientDocument): bool
    {
        return $staff->hasPermission(StaffPermission::ManagePatients);
    }
}
