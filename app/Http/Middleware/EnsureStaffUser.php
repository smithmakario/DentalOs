<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\StaffAuth;
use App\Support\TenantPortal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffUser
{
    public function handle(Request $request, Closure $next): Response
    {
        StaffAuth::reconcileForCurrentTenant($request);

        $staff = Auth::guard('staff')->user();

        if ($staff === null) {
            return $this->redirectToLogin($request);
        }

        if (! $staff->is_active) {
            Auth::guard('staff')->logout();
            StaffAuth::forget($request);

            return $this->redirectToLogin($request)
                ->withErrors(['email' => __('Your account has been deactivated.')]);
        }

        return $next($request);
    }

    private function redirectToLogin(Request $request)
    {
        $tenant = tenant();

        if ($tenant instanceof Tenant) {
            return redirect()->to(TenantPortal::loginUrl($tenant, $request));
        }

        return redirect()->route('tenant.login');
    }
}
