<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ResolvesClinicOrganization;
use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Services\ClinicOverviewService;
use App\Support\TenantPortal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    use ResolvesClinicOrganization;

    public function __construct(private ClinicOverviewService $overview) {}

    public function index(Request $request): View
    {
        $organization = $this->clinicOrganization($request);
        $branchId = $request->string('branch')->toString() ?: null;
        $branches = $this->overview->branches($organization);

        $staffMembers = $this->overview->collectFromBranches(
            $organization,
            $branchId,
            function ($branch) {
                return Staff::query()
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get()
                    ->map(fn (Staff $staff): array => [
                        'id' => $staff->id,
                        'full_name' => $staff->full_name,
                        'email' => $staff->email,
                        'role' => $staff->role,
                        'is_active' => $staff->is_active,
                        'branch_id' => $branch->getTenantKey(),
                        'branch_name' => $branch->name,
                        'branch_url' => TenantPortal::url($branch, '/staff/'.$staff->id),
                    ]);
            },
        )->sortBy('full_name')->values();

        return view('clinic.staff.index', [
            'organization' => $organization,
            'staffMembers' => $staffMembers,
            'branches' => $branches,
            'selectedBranch' => $branchId,
            'totalStaff' => $staffMembers->count(),
            'branchesWithStaff' => $staffMembers->pluck('branch_id')->unique()->count(),
        ]);
    }
}
