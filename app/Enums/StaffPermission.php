<?php

namespace App\Enums;

enum StaffPermission: string
{
    case ViewPatientCharts = 'view_patient_charts';
    case ManagePatients = 'manage_patients';
    case ManageAppointments = 'manage_appointments';
    case ManageTreatments = 'manage_treatments';
    case ManageStaff = 'manage_staff';
    case ManageBranchSettings = 'manage_branch_settings';
    case ManageBilling = 'manage_billing';
}
