<?php

namespace App\Http\Controllers\Central;

use App\Enums\AuditAction;
use App\Enums\OrganizationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreSubscriptionPlanRequest;
use App\Http\Requests\Central\UpdateSubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SubscriptionPlan::class);

        $plans = SubscriptionPlan::query()
            ->orderBy('organization_type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn (SubscriptionPlan $plan): string => $plan->organization_type->value);

        return view('central.subscription-plans.index', [
            'plansByType' => $plans,
            'organizationTypes' => OrganizationType::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', SubscriptionPlan::class);

        return view('central.subscription-plans.create', [
            'plan' => new SubscriptionPlan([
                'is_active' => true,
                'sort_order' => 0,
                'organization_type' => OrganizationType::Single,
            ]),
            'organizationTypes' => OrganizationType::cases(),
        ]);
    }

    public function store(StoreSubscriptionPlanRequest $request): RedirectResponse
    {
        $plan = SubscriptionPlan::query()->create([
            ...$request->safe()->except('is_active'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->auditLogService->record(
            AuditAction::SubscriptionPlanCreated,
            __('Created subscription plan :name.', ['name' => $plan->name]),
            $plan,
            properties: ['organization_type' => $plan->organization_type->value],
        );

        return redirect()
            ->route('subscription-plans.index')
            ->with('status', __('Subscription plan created successfully.'));
    }

    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        $this->authorize('update', $subscriptionPlan);

        return view('central.subscription-plans.edit', [
            'plan' => $subscriptionPlan,
            'organizationTypes' => OrganizationType::cases(),
        ]);
    }

    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $subscriptionPlan->update([
            ...$request->safe()->except('is_active'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->auditLogService->record(
            AuditAction::SubscriptionPlanUpdated,
            __('Updated subscription plan :name.', ['name' => $subscriptionPlan->name]),
            $subscriptionPlan,
            properties: ['is_active' => $subscriptionPlan->is_active],
        );

        return redirect()
            ->route('subscription-plans.index')
            ->with('status', __('Subscription plan updated successfully.'));
    }
}
