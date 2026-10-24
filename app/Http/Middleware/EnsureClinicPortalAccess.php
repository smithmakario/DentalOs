<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\Staff;
use App\Services\ClinicAdminAccess;
use App\Services\ClinicSubscriptionAccess;
use App\Support\ClinicPortal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicPortalAccess
{
    public function __construct(
        private ClinicAdminAccess $clinicAdminAccess,
        private ClinicSubscriptionAccess $clinicSubscriptionAccess,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('staff')->check()) {
            return redirect()->route('clinic.login');
        }

        if (! ClinicPortal::isActive($request)) {
            Auth::guard('staff')->logout();

            return redirect()->route('clinic.login');
        }

        $staffId = Auth::guard('staff')->id();

        // Resolve clinic org on the central connection (never while tenant DB is active).
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        Auth::guard('staff')->forgetUser();

        $organization = Organization::query()->find(ClinicPortal::organizationId($request));

        if ($organization === null || $organization->adminBranch === null) {
            Auth::guard('staff')->logout();
            ClinicPortal::clear($request);

            return redirect()
                ->route('clinic.login')
                ->withErrors(['email' => __('No admin branch is configured for this clinic.')]);
        }

        if (! $this->clinicSubscriptionAccess->allowsStaffAccess($organization)) {
            Auth::guard('staff')->logout();
            ClinicPortal::clear($request);

            return redirect()
                ->route('clinic.login')
                ->withErrors(['email' => $this->clinicSubscriptionAccess->denialReason($organization)]);
        }

        tenancy()->initialize($organization->adminBranch);
        ClinicPortal::applyCentralUrlRoot($request);

        // Re-load staff from the admin-branch tenant DB (session only stores the id).
        $staff = Staff::query()->find($staffId);

        if ($staff === null) {
            Auth::guard('staff')->logout();
            ClinicPortal::clear($request);

            return redirect()
                ->route('clinic.login')
                ->withErrors(['email' => __('Only the primary clinic admin can access this portal.')]);
        }

        Auth::guard('staff')->setUser($staff);

        if (! $staff->is_active) {
            Auth::guard('staff')->logout();
            ClinicPortal::clear($request);

            return redirect()
                ->route('clinic.login')
                ->withErrors(['email' => __('Your account has been deactivated.')]);
        }

        if (! $this->clinicAdminAccess->canManageClinic($staff, $organization->adminBranch)) {
            Auth::guard('staff')->logout();
            ClinicPortal::clear($request);

            return redirect()
                ->route('clinic.login')
                ->withErrors(['email' => __('Only the primary clinic admin can access this portal.')]);
        }

        $request->attributes->set('clinicOrganization', $organization);

        return $next($request);
    }
}
