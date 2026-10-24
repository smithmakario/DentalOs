<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Support\TenantPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectToAdminBranchController extends Controller
{
    use ResolvesClinicOrganization;

    public function __invoke(Request $request): RedirectResponse
    {
        $organization = $this->clinicOrganization($request);
        $adminBranch = $organization->adminBranch;

        abort_unless($adminBranch !== null, 404);

        $target = trim((string) $request->route('path', 'dashboard'), '/');

        if ($target === '') {
            $target = 'dashboard';
        }

        return redirect()->to(TenantPortal::url($adminBranch, '/'.$target, $request));
    }
}
