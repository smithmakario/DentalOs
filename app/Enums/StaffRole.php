<?php

namespace App\Enums;

enum StaffRole: string
{
    case ClinicAdmin = 'clinic_admin';
    case Dentist = 'dentist';
    case Hygienist = 'hygienist';
    case Receptionist = 'receptionist';
    case Nurse = 'nurse';

    public function label(): string
    {
        return match ($this) {
            self::ClinicAdmin => __('Clinic Admin'),
            self::Dentist => __('Dentist'),
            self::Hygienist => __('Hygienist'),
            self::Receptionist => __('Receptionist'),
            self::Nurse => __('Nurse'),
        };
    }

    /**
     * Roles that can be assigned when creating/editing branch staff.
     * Clinic Admin is only assignable on the clinic's primary (admin) branch.
     *
     * @return list<self>
     */
    public static function branchAssignable(bool $onAdminBranch = false): array
    {
        $roles = [
            self::Dentist,
            self::Receptionist,
            self::Nurse,
            self::Hygienist,
        ];

        if ($onAdminBranch) {
            $roles[] = self::ClinicAdmin;
        }

        return $roles;
    }

    /**
     * @return list<self>
     */
    public static function assignableFor(?self $currentRole, bool $onAdminBranch): array
    {
        $roles = self::branchAssignable($onAdminBranch);

        // Synced primary admin on a satellite branch must keep Clinic Admin selectable
        // so profile edits do not strip the role — new Clinic Admins still cannot be created there.
        if ($currentRole === self::ClinicAdmin && ! in_array(self::ClinicAdmin, $roles, true)) {
            $roles[] = self::ClinicAdmin;
        }

        return $roles;
    }
}
