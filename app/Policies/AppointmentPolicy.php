<?php

namespace App\Policies;

use App\Enums\StaffPermission;
use App\Models\Appointment;
use App\Models\Staff;

class AppointmentPolicy
{
    public function viewAny(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManageAppointments);
    }

    public function view(Staff $staff, Appointment $appointment): bool
    {
        return $staff->hasPermission(StaffPermission::ManageAppointments);
    }

    public function create(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManageAppointments);
    }

    public function update(Staff $staff, Appointment $appointment): bool
    {
        return $staff->hasPermission(StaffPermission::ManageAppointments);
    }

    public function delete(Staff $staff, Appointment $appointment): bool
    {
        return $staff->hasPermission(StaffPermission::ManageAppointments);
    }

    public function updateStatus(Staff $staff, Appointment $appointment): bool
    {
        return $staff->hasPermission(StaffPermission::ManageAppointments);
    }
}
