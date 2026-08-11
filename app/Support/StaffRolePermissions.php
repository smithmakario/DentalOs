<?php

namespace App\Support;

use App\Enums\StaffPermission;
use App\Enums\StaffRole;

class StaffRolePermissions
{
    /**
     * @return list<StaffPermission>
     */
    public static function forRole(StaffRole $role): array
    {
        return match ($role) {
            StaffRole::ClinicAdmin => [
                StaffPermission::ViewPatientCharts,
                StaffPermission::ManagePatients,
                StaffPermission::ManageAppointments,
                StaffPermission::ManageTreatments,
                StaffPermission::ManageStaff,
                StaffPermission::ManageBranchSettings,
                StaffPermission::ManageBilling,
            ],
            StaffRole::Dentist => [
                StaffPermission::ViewPatientCharts,
                StaffPermission::ManagePatients,
                StaffPermission::ManageAppointments,
                StaffPermission::ManageTreatments,
            ],
            StaffRole::Hygienist => [
                StaffPermission::ViewPatientCharts,
                StaffPermission::ManageAppointments,
            ],
            StaffRole::Receptionist => [
                StaffPermission::ViewPatientCharts,
                StaffPermission::ManagePatients,
                StaffPermission::ManageAppointments,
                StaffPermission::ManageBilling,
            ],
            StaffRole::Nurse => [
                StaffPermission::ViewPatientCharts,
                StaffPermission::ManageAppointments,
            ],
        };
    }

    public static function roleHas(StaffRole $role, StaffPermission $permission): bool
    {
        return in_array($permission, self::forRole($role), true);
    }

    /**
     * @return list<string>
     */
    public static function permissionLabels(StaffRole $role): array
    {
        return array_map(
            fn (StaffPermission $permission): string => match ($permission) {
                StaffPermission::ViewPatientCharts => __('View patient charts'),
                StaffPermission::ManagePatients => __('Manage patients'),
                StaffPermission::ManageAppointments => __('Manage appointments'),
                StaffPermission::ManageTreatments => __('Manage treatments'),
                StaffPermission::ManageStaff => __('Manage staff'),
                StaffPermission::ManageBranchSettings => __('Manage branch settings'),
                StaffPermission::ManageBilling => __('Manage billing'),
            },
            self::forRole($role),
        );
    }
}
