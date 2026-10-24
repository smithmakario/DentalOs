<?php

namespace App\Http\Controllers\Central;

use App\Enums\AuditAction;
use App\Enums\BillingCycle;
use App\Enums\OrganizationType;
use App\Enums\PlatformRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\AssignAdminBranchRequest;
use App\Http\Requests\Central\AssignClinicSubscriptionRequest;
use App\Http\Requests\Central\StoreClinicRequest;
use App\Http\Requests\Central\UpdateClinicPrimaryAdminRequest;
use App\Http\Requests\Central\UpdateClinicRequest;
use App\Models\BranchProfile;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ClinicOnboardingService;
use App\Services\ClinicPrimaryAdminService;
use App\Services\ClinicSubscriptionAccess;
use App\Support\BranchIdentifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ClinicController extends Controller
{
    public function __construct(
        private ClinicOnboardingService $clinicOnboardingService,
        private ClinicPrimaryAdminService $clinicPrimaryAdminService,
        private ClinicSubscriptionAccess $clinicSubscriptionAccess,
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

        return view('central.clinics.create', [
            'appHost' => BranchIdentifier::baseHost(),
        ]);
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
            ->route('clinics.show', $organization)
            ->with('status', __('Clinic onboarded successfully. Assign a subscription plan to activate it.'));
    }

    public function show(Organization $organization): View
    {
        $this->authorize('view', $organization);

        $organization->loadCount('branches')
            ->load([
                'users',
                'adminBranch',
                'branches' => fn ($query) => $query->latest(),
            ]);

        $subscription = $this->clinicSubscriptionAccess->currentSubscription($organization);

        $plans = SubscriptionPlan::query()
            ->active()
            ->forOrganizationType($organization->type)
            ->orderBy('sort_order')
            ->get();

        $clinicLogoUrl = null;
        if ($organization->adminBranch instanceof Tenant) {
            $clinicLogoUrl = $organization->adminBranch->run(
                fn (): ?string => BranchProfile::query()->first()?->logoUrl(),
            );
        }

        return view('central.clinics.show', [
            'organization' => $organization,
            'clinicLogoUrl' => $clinicLogoUrl,
            'subscription' => $subscription,
            'plans' => $plans,
            'billingCycles' => BillingCycle::cases(),
            'graceDays' => ClinicSubscriptionAccess::PAYMENT_GRACE_DAYS,
            'allowsStaffAccess' => $this->clinicSubscriptionAccess->allowsStaffAccess($organization),
        ]);
    }

    public function assignAdminBranch(
        AssignAdminBranchRequest $request,
        Organization $organization,
    ): RedirectResponse {
        $organization->update([
            'admin_branch_id' => $request->validated('admin_branch_id'),
        ]);

        $this->auditLogService->record(
            AuditAction::ClinicUpdated,
            __('Assigned admin branch for :clinic.', ['clinic' => $organization->name]),
            $organization,
            $organization,
            ['admin_branch_id' => $organization->admin_branch_id],
        );

        return redirect()
            ->route('clinics.show', $organization)
            ->with('status', __('Admin branch updated. Clinic admins who sign in to that branch can manage clinic branches.'));
    }

    public function updatePrimaryAdmin(
        UpdateClinicPrimaryAdminRequest $request,
        Organization $organization,
    ): RedirectResponse {
        $admin = $this->clinicPrimaryAdminService->updateClinicPrimaryAdmin(
            $organization,
            $request->validated(),
        );

        $this->auditLogService->record(
            AuditAction::ClinicUpdated,
            __('Updated primary admin for :clinic to :email.', [
                'clinic' => $organization->name,
                'email' => $admin->email,
            ]),
            $organization,
            $organization,
            ['admin_email' => $admin->email],
        );

        return redirect()
            ->route('clinics.show', $organization)
            ->with('status', __('Primary admin updated successfully.'));
    }

    public function assignSubscription(
        AssignClinicSubscriptionRequest $request,
        Organization $organization,
    ): RedirectResponse {
        $subscription = $this->clinicSubscriptionAccess->assignPlan(
            $organization,
            $request->plan(),
            BillingCycle::from($request->validated('billing_cycle')),
        );

        $this->auditLogService->record(
            AuditAction::SubscriptionAssigned,
            __('Assigned plan :plan to :clinic.', [
                'plan' => $subscription->plan?->name ?? __('Unknown'),
                'clinic' => $organization->name,
            ]),
            $organization,
            $organization,
            [
                'subscription_plan_id' => $subscription->subscription_plan_id,
                'billing_cycle' => $subscription->billing_cycle->value,
            ],
        );

        return redirect()
            ->route('clinics.show', $organization)
            ->with('status', __('Subscription plan assigned. Clinic is active. Payment can be collected now or when the period is due (:days-day grace after due date).', [
                'days' => ClinicSubscriptionAccess::PAYMENT_GRACE_DAYS,
            ]));
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
            ->route('clinics.show', $organization)
            ->with('status', __('Clinic updated successfully.'));
    }

    /**
     * @param  User  $user
     * @return Builder<Organization>
     */
    private function organizationsQueryFor($user): Builder
    {
        return $user->platform_role === PlatformRole::SuperAdmin
            ? Organization::query()
            : $user->organizations()->getQuery();
    }

    /**
     * @param  Collection<int, Organization>  $organizations
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
