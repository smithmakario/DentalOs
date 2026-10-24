<?php

namespace App\Services;

use App\Enums\BillingCycle;
use App\Enums\OrganizationSubscriptionStatus;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClinicSubscriptionAccess
{
    public const PAYMENT_GRACE_DAYS = 5;

    public function currentSubscription(Organization $organization): ?OrganizationSubscription
    {
        return $organization->subscriptions()
            ->with('plan')
            ->whereIn('status', [
                OrganizationSubscriptionStatus::Active->value,
                OrganizationSubscriptionStatus::PastDue->value,
                OrganizationSubscriptionStatus::Pending->value,
            ])
            ->latest('id')
            ->first();
    }

    public function accessSubscription(Organization $organization): ?OrganizationSubscription
    {
        return $organization->subscriptions()
            ->with('plan')
            ->whereIn('status', [
                OrganizationSubscriptionStatus::Active->value,
                OrganizationSubscriptionStatus::PastDue->value,
            ])
            ->latest('id')
            ->first();
    }

    public function accessDeadline(OrganizationSubscription $subscription): ?Carbon
    {
        $periodEnd = $subscription->current_period_end;

        if ($periodEnd === null) {
            return $subscription->created_at?->copy()->addDays(self::PAYMENT_GRACE_DAYS);
        }

        return $periodEnd->copy()->addDays(self::PAYMENT_GRACE_DAYS);
    }

    public function isWithinPaymentGrace(OrganizationSubscription $subscription): bool
    {
        $deadline = $this->accessDeadline($subscription);

        return $deadline !== null && now()->lte($deadline);
    }

    public function allowsStaffAccess(Organization $organization): bool
    {
        if (! $organization->is_active) {
            return false;
        }

        $subscription = $this->accessSubscription($organization);

        if ($subscription === null) {
            return false;
        }

        if (
            $subscription->current_period_end !== null
            && now()->greaterThan($subscription->current_period_end)
            && $subscription->status === OrganizationSubscriptionStatus::Active
        ) {
            $subscription->update([
                'status' => OrganizationSubscriptionStatus::PastDue,
            ]);
            $subscription->refresh();
        }

        return $this->isWithinPaymentGrace($subscription);
    }

    public function denialReason(Organization $organization): string
    {
        if (! $organization->is_active) {
            return __('This clinic is not active yet. Assign a subscription plan first.');
        }

        $subscription = $this->accessSubscription($organization);

        if ($subscription === null) {
            return __('This clinic has no subscription plan. Contact your administrator.');
        }

        if (! $this->isWithinPaymentGrace($subscription)) {
            return __('Access suspended: payment is more than :days days overdue.', [
                'days' => self::PAYMENT_GRACE_DAYS,
            ]);
        }

        return __('Subscription access is unavailable.');
    }

    /**
     * Assign a plan and activate the clinic/branches so staff can use the product.
     * Payment can happen now or when the period is due (with a 5-day grace).
     */
    public function assignPlan(
        Organization $organization,
        SubscriptionPlan $plan,
        BillingCycle $billingCycle,
    ): OrganizationSubscription {
        return DB::transaction(function () use ($organization, $plan, $billingCycle): OrganizationSubscription {
            $organization->subscriptions()
                ->whereIn('status', [
                    OrganizationSubscriptionStatus::Pending->value,
                    OrganizationSubscriptionStatus::Active->value,
                    OrganizationSubscriptionStatus::PastDue->value,
                ])
                ->update(['status' => OrganizationSubscriptionStatus::Cancelled->value]);

            $periodEnd = $billingCycle === BillingCycle::Yearly
                ? now()->addYear()
                : now()->addMonth();

            $subscription = OrganizationSubscription::query()->create([
                'organization_id' => $organization->id,
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'status' => OrganizationSubscriptionStatus::Active,
                'current_period_start' => now(),
                'current_period_end' => $periodEnd,
            ]);

            $organization->update(['is_active' => true]);
            $organization->branches()->update(['is_active' => true]);

            return $subscription->load('plan');
        });
    }

    public function activateOrganization(Organization $organization): void
    {
        $organization->update(['is_active' => true]);
        $organization->branches()->update(['is_active' => true]);
    }

    public function canAddBranch(Organization $organization): bool
    {
        $subscription = $this->currentSubscription($organization);

        if ($subscription === null || $subscription->plan === null) {
            return false;
        }

        $maxBranches = $subscription->plan->max_branches;

        if ($maxBranches === null) {
            return true;
        }

        return $organization->branches()->count() < $maxBranches;
    }
}
