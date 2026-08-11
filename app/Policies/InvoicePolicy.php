<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Enums\StaffPermission;
use App\Models\Invoice;
use App\Models\Staff;

class InvoicePolicy
{
    public function viewAny(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBilling)
            || $staff->hasPermission(StaffPermission::ViewPatientCharts);
    }

    public function view(Staff $staff, Invoice $invoice): bool
    {
        return $this->viewAny($staff);
    }

    public function create(Staff $staff): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBilling);
    }

    public function update(Staff $staff, Invoice $invoice): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBilling)
            && $invoice->status !== InvoiceStatus::Void;
    }

    public function delete(Staff $staff, Invoice $invoice): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBilling)
            && $invoice->payments()->count() === 0;
    }

    public function recordPayment(Staff $staff, Invoice $invoice): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBilling)
            && ! in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Void], true);
    }

    public function void(Staff $staff, Invoice $invoice): bool
    {
        return $staff->hasPermission(StaffPermission::ManageBilling)
            && $invoice->status !== InvoiceStatus::Void;
    }
}
