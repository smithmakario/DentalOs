<?php

namespace App\Policies;

use App\Enums\StaffPermission;
use App\Models\Staff;

class StaffPolicy
{
    public function viewAny(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManageStaff);
    }

    public function view(Staff $staff, Staff $model): bool
    {
        return $staff->hasPermission(StaffPermission::ManageStaff);
    }

    public function create(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManageStaff);
    }

    public function update(Staff $staff, Staff $model): bool
    {
        return $staff->hasPermission(StaffPermission::ManageStaff);
    }

    public function delete(Staff $staff, Staff $model): bool
    {
        if ($staff->id === $model->id) {
            return false;
        }

        return $staff->hasPermission(StaffPermission::ManageStaff);
    }
}
