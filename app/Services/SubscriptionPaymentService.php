<?php

namespace App\Services;

use App\Enums\BillingCycle;
use App\Enums\OrganizationSubscriptionStatus;
use App\Enums\PlatformPaymentMethod;
use App\Enums\SubscriptionPaymentStatus;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\PlatformPaymentSetting;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SubscriptionPaymentService
{
    public function __construct(
        private PaystackService $paystackService,
    ) {}

    /**
     * @return array{redirect_url: string}
     */
    public function initiatePaystackCheckout(
        Organization $organization,
        SubscriptionPlan $plan,
        BillingCycle $billingCycle,
        User $user,
    ): array {
        if (! $this->paystackService->isConfigured()) {
            throw new RuntimeException(__('Paystack is not configured.'));
        }

        $amount = $plan->priceFor($billingCycle);
        $reference = SubscriptionPayment::generatePaystackReference();

        $subscription = $this->createPendingSubscription($organization, $plan, $billingCycle);

        $payment = SubscriptionPayment::query()->create([
            'organization_subscription_id' => $subscription->id,
            'organization_id' => $organization->id,
            'amount' => $amount,
            'currency' => config('paystack.currency', 'NGN'),
            'payment_method' => PlatformPaymentMethod::Paystack,
            'status' => SubscriptionPaymentStatus::Pending,
            'paystack_reference' => $reference,
        ]);

        $initialized = $this->paystackService->initializeTransaction(
            email: $user->email,
            amountInKobo: $this->paystackService->amountToKobo($amount),
            reference: $reference,
            callbackUrl: route('paystack.callback'),
            metadata: [
                'organization_id' => $organization->id,
                'subscription_payment_id' => $payment->id,
            ],
        );

        $payment->update([
            'paystack_access_code' => $initialized['access_code'],
        ]);

        return [
            'redirect_url' => $initialized['authorization_url'],
        ];
    }

    public function submitManualPayment(
        Organization $organization,
        SubscriptionPlan $plan,
        BillingCycle $billingCycle,
        string $paymentReference,
        ?string $notes,
    ): SubscriptionPayment {
        $settings = PlatformPaymentSetting::current();

        if ($settings === null || ! $settings->isConfigured()) {
            throw new RuntimeException(__('Manual payment bank details are not configured.'));
        }

        $subscription = $this->createPendingSubscription($organization, $plan, $billingCycle);

        return SubscriptionPayment::query()->create([
            'organization_subscription_id' => $subscription->id,
            'organization_id' => $organization->id,
            'amount' => $plan->priceFor($billingCycle),
            'currency' => $settings->currency,
            'payment_method' => PlatformPaymentMethod::Manual,
            'status' => SubscriptionPaymentStatus::AwaitingVerification,
            'manual_payment_reference' => $paymentReference,
            'manual_notes' => $notes,
        ]);
    }

    public function completePaystackPayment(string $reference): ?SubscriptionPayment
    {
        $payment = SubscriptionPayment::query()
            ->where('paystack_reference', $reference)
            ->where('payment_method', PlatformPaymentMethod::Paystack)
            ->first();

        if ($payment === null) {
            return null;
        }

        if ($payment->status === SubscriptionPaymentStatus::Completed) {
            return $payment;
        }

        $verification = $this->paystackService->verifyTransaction($reference);

        if ($verification['status'] !== 'success') {
            $payment->update(['status' => SubscriptionPaymentStatus::Failed]);

            return $payment;
        }

        return $this->markPaymentCompleted($payment);
    }

    public function verifyManualPayment(SubscriptionPayment $payment, User $verifier): SubscriptionPayment
    {
        if ($payment->payment_method !== PlatformPaymentMethod::Manual) {
            throw new RuntimeException(__('Only manual payments can be verified.'));
        }

        if ($payment->status !== SubscriptionPaymentStatus::AwaitingVerification) {
            throw new RuntimeException(__('This payment is not awaiting verification.'));
        }

        $payment->verified_by = $verifier->id;

        return $this->markPaymentCompleted($payment);
    }

    private function createPendingSubscription(
        Organization $organization,
        SubscriptionPlan $plan,
        BillingCycle $billingCycle,
    ): OrganizationSubscription {
        $organization->subscriptions()
            ->where('status', OrganizationSubscriptionStatus::Pending)
            ->delete();

        return OrganizationSubscription::query()->create([
            'organization_id' => $organization->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'status' => OrganizationSubscriptionStatus::Pending,
        ]);
    }

    private function markPaymentCompleted(SubscriptionPayment $payment): SubscriptionPayment
    {
        return DB::transaction(function () use ($payment): SubscriptionPayment {
            $payment->update([
                'status' => SubscriptionPaymentStatus::Completed,
                'paid_at' => now(),
            ]);

            $subscription = $payment->subscription()->firstOrFail();
            $periodEnd = $subscription->billing_cycle === BillingCycle::Yearly
                ? now()->addYear()
                : now()->addMonth();

            $organization = $payment->organization;

            $organization->subscriptions()
                ->where('id', '!=', $subscription->id)
                ->where('status', OrganizationSubscriptionStatus::Active)
                ->update(['status' => OrganizationSubscriptionStatus::Cancelled]);

            $subscription->update([
                'status' => OrganizationSubscriptionStatus::Active,
                'current_period_start' => now(),
                'current_period_end' => $periodEnd,
            ]);

            return $payment->fresh(['subscription.plan', 'organization']);
        });
    }
}
