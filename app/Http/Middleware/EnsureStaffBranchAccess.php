<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\Tenant;
use App\Services\ClinicSubscriptionAccess;
use App\Services\StaffAccessService;
use App\Support\TenantPortal;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffBranchAccess
{
    public function __construct(
        private StaffAccessService $staffAccessService,
        private ClinicSubscriptionAccess $clinicSubscriptionAccess,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('staff')->user();

        if ($staff === null) {
            return $this->redirectToLogin($request);
        }

        $organization = tenant()?->organization;

        if ($organization instanceof Organization && ! $this->clinicSubscriptionAccess->allowsStaffAccess($organization)) {
            Auth::guard('staff')->logout();

            return $this->redirectToLogin($request)
                ->withErrors(['email' => $this->clinicSubscriptionAccess->denialReason($organization)]);
        }

        if ($staff->organization_staff_id === null) {
            return $next($request);
        }

        $staffMember = $this->staffAccessService->findMemberForTenantStaff(
            $staff->organization_staff_id,
            $staff->email,
        );

        if ($staffMember === null || ! $this->staffAccessService->canAccessTenant($staffMember, (string) tenant('id'))) {
            Auth::guard('staff')->logout();

            return $this->redirectToLogin($request)
                ->withErrors(['email' => __('You do not have access to this branch.')]);
        }

        $request->attributes->set('staff_member', $staffMember);

        return $next($request);
    }

    private function redirectToLogin(Request $request): RedirectResponse
    {
        $tenant = tenant();

        if ($tenant instanceof Tenant) {
            return redirect()->to(TenantPortal::loginUrl($tenant, $request));
        }

        return redirect()->route('tenant.login');
    }
}
