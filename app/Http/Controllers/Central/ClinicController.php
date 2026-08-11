<?php

namespace App\Http\Controllers\Central;

use App\Enums\AuditAction;
use App\Enums\OrganizationType;
use App\Enums\PlatformRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreClinicRequest;
use App\Http\Requests\Central\UpdateClinicRequest;
use App\Models\Organization;
use App\Models\Tenant;
use App\Services\AuditLogService;
use App\Services\ClinicOnboardingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicController extends Controller
{
    public function __construct(
        private ClinicOnboardingService $clinicOnboardingService,
        private AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Organization::class);

        $organizationsQuery = $this->organizationsQueryFor($request->user());

        $totalClinics = (clone $organizationsQuery)->count();

        $organizationIds = (clone $organizationsQuery)->pluck('id');

        $totalBranches = Tenant::query()
            ->when($organizationIds->isNotEmpty(), fn (Builder $query) => $query->whereIn('organization_id', $organizationIds))
            ->when($organizationIds->isEmpty(), fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->count();

        $dominantTier = $this->dominantTierLabel((clone $organizationsQuery)->get(['type']));

        $organizations = (clone $organizationsQuery)
            ->withCount('branches')
            ->with(['users', 'activeSubscription.plan'])
            ->when($request->filled('tier'), function (Builder $query) use ($request): void {
                match ($request->string('tier')->toString()) {
                    'enterprise' => $query->where('type', OrganizationType::Dso),
                    'professional', 'standard' => $query->where('type', OrganizationType::Single),
                    default => null,
                };
            })
            ->when($request->filled('status'), function (Builder $query) use ($request): void {
                $query->where('is_active', $request->string('status')->toString() === 'active');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('central.clinics.index', [
            'organizations' => $organizations,
            'totalClinics' => $totalClinics,
            'totalBranches' => $totalBranches,
            'dominantTier' => $dominantTier,
            'filters' => [
                'tier' => $request->string('tier')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Organization::class);

        return view('central.clinics.create');
    }

    public function store(StoreClinicRequest $request): RedirectResponse
    {
        $organization = $this->clinicOnboardingService->onboard($request->validated());

        $this->auditLogService->record(
            AuditAction::ClinicOnboarded,
            __('Onboarded clinic :name.', ['name' => $organization->name]),
            $organization,
            $organization,
            ['slug' => $organization->slug],
        );

        return redirect()
            ->route('clinics.edit', $organization)
            ->with('status', __('Clinic onboarded successfully.'));
    }

    public function edit(Organization $organization): View
    {
        $this->authorize('update', $organization);

        $organization->loadCount('branches')->load('users');

        return view('central.clinics.edit', [
            'organization' => $organization,
        ]);
    }

    public function update(UpdateClinicRequest $request, Organization $organization): RedirectResponse
    {
        $organization->update($request->validated());

        $this->auditLogService->record(
            AuditAction::ClinicUpdated,
            __('Updated clinic :name.', ['name' => $organization->name]),
            $organization,
            $organization,
            $request->validated(),
        );

        return redirect()
            ->route('clinics.edit', $organization)
            ->with('status', __('Clinic updated successfully.'));
    }

    /**
     * @param  \App\Models\User  $user
     * @return Builder<Organization>
     */
    private function organizationsQueryFor($user): Builder
    {
        return $user->platform_role === PlatformRole::SuperAdmin
            ? Organization::query()
            : $user->organizations()->getQuery();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Organization>  $organizations
     */
    private function dominantTierLabel($organizations): string
    {
        if ($organizations->isEmpty()) {
            return '—';
        }

        $dsoCount = $organizations->where('type', OrganizationType::Dso)->count();
        $singleCount = $organizations->where('type', OrganizationType::Single)->count();

        if ($dsoCount >= $singleCount) {
            return __('Enterprise');
        }

        return __('Professional');
    }
}
