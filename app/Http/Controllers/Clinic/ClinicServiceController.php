<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Models\ClinicService;
use App\Services\ClinicOverviewService;
use App\Support\TenantPortal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicServiceController extends Controller
{
    use ResolvesClinicOrganization;

    public function __construct(private ClinicOverviewService $overview) {}

    public function index(Request $request): View
    {
        $organization = $this->clinicOrganization($request);
        $branchId = $request->string('branch')->toString() ?: null;
        $branches = $this->overview->branches($organization);

        $services = $this->overview->collectFromBranches(
            $organization,
            $branchId,
            function ($branch) {
                return ClinicService::query()
                    ->orderBy('name')
                    ->get()
                    ->map(fn (ClinicService $service): array => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'code' => $service->code,
                        'price' => $service->price,
                        'is_active' => $service->is_active,
                        'branch_id' => $branch->getTenantKey(),
                        'branch_name' => $branch->name,
                        'branch_url' => TenantPortal::url($branch, '/clinic-services'),
                    ]);
            },
        )->sortBy('name')->values();

        return view('clinic.services.index', [
            'organization' => $organization,
            'services' => $services,
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'totalServices' => $services->count(),
            'branchesWithServices' => $services->pluck('branch_id')->unique()->count(),
        ]);
    }
}
