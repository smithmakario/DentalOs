<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StaffLoginRequest;
use App\Models\BranchProfile;
use App\Models\Tenant;
use App\Services\ClinicAdminAccess;
use App\Services\PrimaryClinicAdminProvisioner;
use App\Support\ClinicPortal;
use App\Support\StaffAuth;
use App\Support\TenantPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        $tenant = tenant();
        $tenant?->loadMissing('organization');

        $staff = Auth::guard('staff')->user();
        $clinicName = $tenant?->organization?->name ?? $tenant?->name;
        $branchProfile = BranchProfile::query()->first();

        return view('tenant.auth.login', [
            'clinicName' => $clinicName,
            'branchName' => $tenant?->name,
            'branchLogoUrl' => $branchProfile?->logoUrl(),
            'signedInStaff' => $staff,
            'dashboardUrl' => $tenant instanceof Tenant
                ? TenantPortal::absolute($tenant, '/dashboard')
                : null,
        ]);
    }

    public function store(StaffLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $staff = auth('staff')->user();
        StaffAuth::remember($request, $staff);

        $tenant = tenant();
        $access = app(ClinicAdminAccess::class);

        // Primary admin keeps a clinic-portal session so they can switch branches without re-login.
        if ($tenant instanceof Tenant && $access->canManageClinic($staff, $tenant)) {
            $organization = $access->organization($tenant);

            if ($organization !== null) {
                ClinicPortal::mark($request, $organization);
                app(PrimaryClinicAdminProvisioner::class)->syncToAllBranches($organization);
            }
        }

        $dashboard = $tenant instanceof Tenant
            ? TenantPortal::absolute($tenant, '/dashboard', $request)
            : route('tenant.dashboard');

        return redirect()->intended($dashboard);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('staff')->logout();
        ClinicPortal::clear($request);
        StaffAuth::forget($request);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $tenant = tenant();

        if ($tenant instanceof Tenant) {
            return redirect()->to(TenantPortal::absolute($tenant, '/staff/login', $request));
        }

        return redirect()->route('tenant.login');
    }
}
