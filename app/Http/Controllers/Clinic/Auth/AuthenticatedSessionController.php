<?php

namespace App\Http\Controllers\Clinic\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinic\ClinicLoginRequest;
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
        return view('clinic.auth.login');
    }

    public function store(ClinicLoginRequest $request): RedirectResponse
    {
        $organization = $request->authenticate();
        $adminBranch = $organization->adminBranch;

        abort_unless($adminBranch !== null, 404);

        $request->session()->regenerate();
        ClinicPortal::mark($request, $organization);
        StaffAuth::remember($request, Auth::guard('staff')->user());
        app(PrimaryClinicAdminProvisioner::class)->syncToAllBranches($organization);

        // Always land on /{clinic}/{branch}/dashboard — never leave them on /clinic/*.
        return redirect()->to(TenantPortal::url($adminBranch, '/dashboard', $request));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('staff')->logout();
        ClinicPortal::clear($request);
        StaffAuth::forget($request);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        return redirect()->route('clinic.login');
    }
}
