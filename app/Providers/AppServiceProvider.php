<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\BranchProfile;
use App\Models\ClinicService;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\Staff;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanOption;
use App\Observers\PatientObserver;
use App\Policies\AppointmentPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\ClinicServicePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PatientDocumentPolicy;
use App\Policies\PatientPolicy;
use App\Policies\StaffPolicy;
use App\Policies\TreatmentPlanOptionPolicy;
use App\Policies\TreatmentPlanPolicy;
use App\Services\ClinicAdminAccess;
use App\Services\NotificationFeedService;
use App\Services\StaffAccessService;
use App\Support\ClinicPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        if (! $this->app->runningInConsole() && filled(request()->getHost())) {
            // Prefer the request origin so local 127.0.0.1:port stays correct
            // when APP_URL is http://localhost (or another host).
            URL::forceRootUrl(rtrim(request()->getSchemeAndHttpHost(), '/'));

            $host = request()->getHost();

            if (in_array($host, config('tenancy.central_domains'), true)) {
                URL::defaults(['domain' => $host]);
            }
        }

        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(TreatmentPlan::class, TreatmentPlanPolicy::class);
        Gate::policy(TreatmentPlanOption::class, TreatmentPlanOptionPolicy::class);
        Gate::policy(PatientDocument::class, PatientDocumentPolicy::class);
        Gate::policy(ClinicService::class, ClinicServicePolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Staff::class, StaffPolicy::class);

        Patient::observe(PatientObserver::class);

        View::composer('layouts.app', function ($view): void {
            $user = Auth::user();

            if ($user === null) {
                $view->with('platformNotifications', collect());
                $view->with('platformNotificationUnreadCount', 0);

                return;
            }

            $feed = app(NotificationFeedService::class)->forPlatformUser($user);

            $view->with('platformNotifications', $feed['items']);
            $view->with('platformNotificationUnreadCount', $feed['unread_count']);
        });

        View::composer(['layouts.clinic-navigation', 'layouts.clinic'], function ($view): void {
            $staff = Auth::guard('staff')->user();
            $organization = request()->attributes->get('clinicOrganization');

            if (! $organization instanceof Organization) {
                $organizationId = ClinicPortal::organizationId();
                $organization = $organizationId
                    ? Organization::query()->find($organizationId)
                    : null;
            }

            $view->with('clinicOrganization', $organization);
            $view->with('clinicStaff', $staff);

            if ($staff === null) {
                $view->with('tenantNotifications', collect());
                $view->with('tenantNotificationUnreadCount', 0);

                return;
            }

            $feed = app(NotificationFeedService::class)->forStaff($staff);

            $view->with('tenantNotifications', $feed['items']);
            $view->with('tenantNotificationUnreadCount', $feed['unread_count']);
        });

        View::composer(['layouts.tenant-navigation', 'layouts.tenant', 'components.tenant.working-branch'], function ($view): void {
            if (tenancy()->initialized) {
                $branchProfile = BranchProfile::query()->first();
                $view->with('branchProfile', $branchProfile);
                $view->with('branchLogoUrl', $branchProfile?->logoUrl());
            } else {
                $view->with('branchProfile', null);
                $view->with('branchLogoUrl', null);
            }

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
            $view->with('staffCanManageClinic', app(ClinicAdminAccess::class)->canManageClinic($staff));

            if ($staff === null) {
                $view->with('tenantNotifications', collect());
                $view->with('tenantNotificationUnreadCount', 0);

                return;
            }

            $feed = app(NotificationFeedService::class)->forStaff($staff);

            $view->with('tenantNotifications', $feed['items']);
            $view->with('tenantNotificationUnreadCount', $feed['unread_count']);
        });

        View::composer(['layouts.tenant-navigation', 'layouts.tenant', 'components.tenant.working-branch'], function ($view): void {
            $staff = Auth::guard('staff')->user();
            $access = app(ClinicAdminAccess::class);
            $organization = $access->organization();

            if ($staff !== null && $access->canManageClinic($staff) && $organization !== null) {
                $view->with('organization', $organization);
                $view->with(
                    'clinicBranches',
                    $organization->branches()->orderBy('name')->get(),
                );
                $view->with(
                    'switchableBranches',
                    $organization->branches()
                        ->orderBy('name')
                        ->get()
                        ->filter(fn ($branch) => (string) $branch->getTenantKey() !== (string) tenant('id'))
                        ->values(),
                );

                return;
            }

            $view->with('clinicBranches', collect());

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
