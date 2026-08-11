<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\ClinicService;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\Staff;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanOption;
use App\Policies\AppointmentPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\ClinicServicePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PatientDocumentPolicy;
use App\Policies\PatientPolicy;
use App\Policies\StaffPolicy;
use App\Policies\TreatmentPlanOptionPolicy;
use App\Policies\TreatmentPlanPolicy;
use App\Services\StaffAccessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(TreatmentPlan::class, TreatmentPlanPolicy::class);
        Gate::policy(TreatmentPlanOption::class, TreatmentPlanOptionPolicy::class);
        Gate::policy(PatientDocument::class, PatientDocumentPolicy::class);
        Gate::policy(ClinicService::class, ClinicServicePolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Staff::class, StaffPolicy::class);

        View::composer(['layouts.tenant-navigation', 'layouts.tenant'], function ($view): void {
            $staff = Auth::guard('staff')->user();

            $view->with('staffCanViewPatients', $staff?->can('viewAny', Patient::class) ?? false);
            $view->with('staffCanManagePatients', $staff?->can('create', Patient::class) ?? false);
            $view->with('staffCanManageAppointments', $staff?->can('viewAny', Appointment::class) ?? false);
            $view->with('staffCanManageTreatments', $staff?->can('create', TreatmentPlan::class) ?? false);
            $view->with('staffCanViewTreatments', $staff?->can('viewAny', TreatmentPlan::class) ?? false);
            $view->with('staffCanManageServices', $staff?->can('create', ClinicService::class) ?? false);
            $view->with('staffCanManageBilling', $staff?->can('create', Invoice::class) ?? false);
            $view->with('staffCanViewBilling', $staff?->can('viewAny', Invoice::class) ?? false);
            $view->with('staffCanManageStaff', $staff?->can('viewAny', Staff::class) ?? false);
        });

        View::composer('layouts.tenant-navigation', function ($view): void {
            $staff = Auth::guard('staff')->user();

            if ($staff === null || $staff->organization_staff_id === null) {
                $view->with('switchableBranches', collect());

                return;
            }

            $staffMember = app(StaffAccessService::class)->findMemberForTenantStaff(
                $staff->organization_staff_id,
                $staff->email,
            );

            if ($staffMember === null) {
                $view->with('switchableBranches', collect());

                return;
            }

            $branches = app(StaffAccessService::class)
                ->accessibleBranches($staffMember)
                ->filter(fn ($branch) => $branch->id !== tenant('id'));

            $view->with('switchableBranches', $branches);
        });
    }
}
