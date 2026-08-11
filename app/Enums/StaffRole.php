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
     * @return list<self>
     */
    public static function branchAssignable(): array
    {
        return [
            self::Dentist,
            self::Receptionist,
            self::Nurse,
            self::Hygienist,
            self::ClinicAdmin,
        ];
    }
}
