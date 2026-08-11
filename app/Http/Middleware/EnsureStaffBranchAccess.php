<?php

namespace App\Http\Middleware;

use App\Services\StaffAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffBranchAccess
{
    public function __construct(
        private StaffAccessService $staffAccessService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('staff')->user();

        if ($staff === null) {
            return redirect()->route('tenant.login');
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

            return redirect()
                ->route('tenant.login')
                ->withErrors(['email' => __('You do not have access to this branch.')]);
        }

        $request->attributes->set('staff_member', $staffMember);

        return $next($request);
    }
}
