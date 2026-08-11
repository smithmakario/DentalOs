<?php

namespace App\Http\Controllers\Central;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreBranchRequest;
use App\Models\Organization;
use App\Services\AuditLogService;
use App\Services\ClinicOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct(
        private ClinicOnboardingService $clinicOnboardingService,
        private AuditLogService $auditLogService,
    ) {}

    public function index(Organization $organization): View
    {
        $this->authorize('manageBranches', $organization);

        $branches = $organization->branches()
            ->with('domains')
            ->latest()
            ->get();

        return view('central.clinics.branches.index', [
            'organization' => $organization,
            'branches' => $branches,
        ]);
    }

    public function store(StoreBranchRequest $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validated();

        $this->clinicOnboardingService->addBranch($organization, [
            'branch_name' => $validated['branch_name'],
            'branch_slug' => $validated['branch_slug'],
            'domain' => $validated['domain'],
            'contact_email' => $organization->email,
        ]);

        $this->auditLogService->record(
            AuditAction::BranchCreated,
            __('Created branch :name for :clinic.', [
                'name' => $validated['branch_name'],
                'clinic' => $organization->name,
            ]),
            $organization,
            $organization,
            $validated,
        );

        return redirect()
            ->route('clinics.branches.index', $organization)
            ->with('status', __('Branch created successfully.'));
    }
}
