<?php

namespace App\Http\Controllers\Central;

use App\Enums\AuditAction;
use App\Enums\BillingCycle;
use App\Enums\PlatformPaymentMethod;
use App\Enums\PlatformRole;
use App\Enums\SubscriptionPaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\InitiateSubscriptionCheckoutRequest;
use App\Http\Requests\Central\SubmitManualPaymentRequest;
use App\Models\Organization;
use App\Models\PlatformPaymentSetting;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Services\AuditLogService;
use App\Services\SubscriptionPaymentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionPaymentService $subscriptionPaymentService,
        private AuditLogService $auditLogService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        $organizationsQuery = $user->platform_role === PlatformRole::SuperAdmin
            ? Organization::query()
            : $user->organizations()->getQuery();

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $organizations = (clone $organizationsQuery)
            ->with(['activeSubscription.plan'])
            ->orderBy('name')
            ->get();

        $pendingPayments = SubscriptionPayment::query()
            ->with(['organization', 'subscription.plan'])
            ->where('status', SubscriptionPaymentStatus::AwaitingVerification)
            ->when($user->platform_role !== PlatformRole::SuperAdmin, function (Builder $query) use ($user): void {
                $query->whereIn('organization_id', $user->organizations()->pluck('organizations.id'));
            })
            ->latest()
            ->get();

        $recentPayments = SubscriptionPayment::query()
            ->with(['organization', 'subscription.plan'])
            ->when($user->platform_role !== PlatformRole::SuperAdmin, function (Builder $query) use ($user): void {
                $query->whereIn('organization_id', $user->organizations()->pluck('organizations.id'));
            })
            ->latest()
            ->limit(10)
            ->get();

        return view('central.subscriptions.index', [
            'plans' => $plans,
            'organizations' => $organizations,
            'pendingPayments' => $pendingPayments,
            'recentPayments' => $recentPayments,
            'paymentSettings' => PlatformPaymentSetting::current(),
            'paystackConfigured' => filled(config('paystack.secret_key')) && filled(config('paystack.public_key')),
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function checkout(Organization $organization): View
    {
        $this->authorize('update', $organization);

        $plans = SubscriptionPlan::query()
            ->active()
            ->forOrganizationType($organization->type)
            ->orderBy('sort_order')
            ->get();

        return view('central.subscriptions.checkout', [
            'organization' => $organization->load('activeSubscription.plan'),
            'plans' => $plans,
            'paymentSettings' => PlatformPaymentSetting::current(),
            'paystackConfigured' => filled(config('paystack.secret_key')) && filled(config('paystack.public_key')),
        ]);
    }

    public function initiate(InitiateSubscriptionCheckoutRequest $request, Organization $organization): RedirectResponse
    {
        $plan = SubscriptionPlan::query()->findOrFail($request->validated('subscription_plan_id'));
        $billingCycle = BillingCycle::from($request->validated('billing_cycle'));
        $paymentMethod = PlatformPaymentMethod::from($request->validated('payment_method'));

        try {
            if ($paymentMethod === PlatformPaymentMethod::Paystack) {
                $result = $this->subscriptionPaymentService->initiatePaystackCheckout(
                    organization: $organization,
                    plan: $plan,
                    billingCycle: $billingCycle,
                    user: $request->user(),
                );

                return redirect()->away($result['redirect_url']);
            }

            return redirect()->route('subscriptions.manual.create', [
                'organization' => $organization,
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => $billingCycle->value,
            ]);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['checkout' => $exception->getMessage()]);
        }
    }

    public function manualForm(Organization $organization): View
    {
        $this->authorize('update', $organization);

        $plan = SubscriptionPlan::query()->findOrFail(request()->integer('subscription_plan_id'));
        $billingCycle = BillingCycle::from(request()->string('billing_cycle')->toString());
        $settings = PlatformPaymentSetting::current();

        abort_unless($plan->organization_type === $organization->type, 404);
        abort_unless($settings?->isConfigured(), 404);

        return view('central.subscriptions.manual', [
            'organization' => $organization,
            'plan' => $plan,
            'billingCycle' => $billingCycle,
            'amount' => $plan->priceFor($billingCycle),
            'settings' => $settings,
        ]);
    }

    public function submitManual(SubmitManualPaymentRequest $request, Organization $organization): RedirectResponse
    {
        $plan = SubscriptionPlan::query()->findOrFail($request->validated('subscription_plan_id'));
        $billingCycle = BillingCycle::from($request->validated('billing_cycle'));

        try {
            $payment = $this->subscriptionPaymentService->submitManualPayment(
                organization: $organization,
                plan: $plan,
                billingCycle: $billingCycle,
                paymentReference: $request->validated('manual_payment_reference'),
                notes: $request->validated('manual_notes'),
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['manual' => $exception->getMessage()]);
        }

        $this->auditLogService->record(
            AuditAction::SubscriptionPaymentSubmitted,
            __('Submitted manual payment for :clinic.', ['clinic' => $organization->name]),
            $payment,
            $organization,
            ['reference' => $payment->manual_payment_reference, 'amount' => $payment->amount],
        );

        return redirect()
            ->route('subscriptions.index')
            ->with('status', __('Manual payment submitted. Awaiting verification.'));
    }

    public function verifyManual(SubscriptionPayment $payment): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        try {
            $this->subscriptionPaymentService->verifyManualPayment($payment, auth()->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        $this->auditLogService->record(
            AuditAction::SubscriptionPaymentVerified,
            __('Verified manual payment for :clinic.', ['clinic' => $payment->organization->name]),
            $payment,
            $payment->organization,
            ['reference' => $payment->manual_payment_reference, 'amount' => $payment->amount],
        );

        return back()->with('status', __('Manual payment verified and subscription activated.'));
    }
}
