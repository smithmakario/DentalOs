<?php

namespace App\Services;

use App\Enums\OrganizationSubscriptionStatus;
use App\Enums\PlatformRole;
use App\Enums\SubscriptionPaymentStatus;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\StaffMember;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlatformAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $context = $this->buildContext($user);

        return [
            'isSuperAdmin' => $context['isSuperAdmin'],
            'totalOrganizations' => (clone $context['organizationsQuery'])->count(),
            'activeOrganizations' => (clone $context['organizationsQuery'])->where('is_active', true)->count(),
            'totalBranches' => (clone $context['branchesQuery'])->count(),
            'activeBranches' => (clone $context['branchesQuery'])->where('is_active', true)->count(),
            'totalStaff' => (clone $context['staffQuery'])->count(),
            'activeStaff' => (clone $context['staffQuery'])->where('is_active', true)->count(),
            'activeSubscriptions' => $context['activeSubscriptions']->count(),
            'totalRevenue' => (clone $context['completedPaymentsQuery'])->sum('amount'),
            'revenueLast30Days' => (clone $context['completedPaymentsQuery'])
                ->where('paid_at', '>=', now()->subDays(30))
                ->sum('amount'),
            'pendingPayments' => (clone $context['paymentsQuery'])
                ->whereIn('status', [
                    SubscriptionPaymentStatus::Pending,
                    SubscriptionPaymentStatus::AwaitingVerification,
                ])
                ->count(),
            'failedPayments' => (clone $context['paymentsQuery'])
                ->where('status', SubscriptionPaymentStatus::Failed)
                ->count(),
            'monthlyTrends' => $this->monthlyTrends($context['organizationsQuery'], $context['completedPaymentsQuery']),
            'planDistribution' => $this->planDistribution($context['activeSubscriptions']),
            'paymentMethodBreakdown' => $this->paymentMethodBreakdown($context['completedPaymentsQuery']),
            'recentOnboardings' => (clone $context['organizationsQuery'])
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'created_at', 'is_active']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardForUser(User $user): array
    {
        $context = $this->buildContext($user);
        $analytics = $this->forUser($user);

        $currentPeriodStart = now()->subDays(30);
        $previousPeriodStart = now()->subDays(60);
        $previousPeriodEnd = now()->subDays(30);

        $branchesCurrent = (clone $context['branchesQuery'])
            ->where('created_at', '>=', $currentPeriodStart)
            ->count();
        $branchesPrevious = (clone $context['branchesQuery'])
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->count();

        $organizationsCurrent = (clone $context['organizationsQuery'])
            ->where('created_at', '>=', $currentPeriodStart)
            ->count();
        $organizationsPrevious = (clone $context['organizationsQuery'])
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->count();

        $revenueCurrent = (clone $context['completedPaymentsQuery'])
            ->where('paid_at', '>=', $currentPeriodStart)
            ->sum('amount');
        $revenuePrevious = (clone $context['completedPaymentsQuery'])
            ->whereBetween('paid_at', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('amount');

        $activeBranchesCurrent = (clone $context['branchesQuery'])->where('is_active', true)->count();
        $activeBranchesPrevious = (clone $context['branchesQuery'])
            ->where('is_active', true)
            ->where('updated_at', '<', $currentPeriodStart)
            ->count();

        $onboardingGrowth = $this->periodChange($organizationsCurrent, $organizationsPrevious);

        return array_merge($analytics, [
            'branchTrend' => $this->periodChange($branchesCurrent, $branchesPrevious),
            'organizationTrend' => $this->periodChange($organizationsCurrent, $organizationsPrevious),
            'revenueTrend' => $this->periodChange((float) $revenueCurrent, (float) $revenuePrevious),
            'activeBranchTrend' => $this->periodChange($activeBranchesCurrent, max(1, $activeBranchesPrevious)),
            'onboardingGrowthRate' => $onboardingGrowth,
            'planMix' => $this->planMixPercentages($context['activeSubscriptions']),
            'onboardingChart' => $analytics['monthlyTrends'],
        ]);
    }

    public function exportCsvForUser(User $user): StreamedResponse
    {
        $metrics = $this->forUser($user);
        $filename = 'dentaflow-analytics-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($metrics): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Total Revenue', $metrics['totalRevenue']]);
            fputcsv($handle, ['Revenue (Last 30 Days)', $metrics['revenueLast30Days']]);
            fputcsv($handle, ['Organizations', $metrics['totalOrganizations']]);
            fputcsv($handle, ['Active Organizations', $metrics['activeOrganizations']]);
            fputcsv($handle, ['Branches', $metrics['totalBranches']]);
            fputcsv($handle, ['Active Branches', $metrics['activeBranches']]);
            fputcsv($handle, ['Staff Members', $metrics['totalStaff']]);
            fputcsv($handle, ['Active Staff', $metrics['activeStaff']]);
            fputcsv($handle, ['Active Subscriptions', $metrics['activeSubscriptions']]);
            fputcsv($handle, ['Pending Payments', $metrics['pendingPayments']]);
            fputcsv($handle, ['Failed Payments', $metrics['failedPayments']]);

            fputcsv($handle, []);
            fputcsv($handle, ['Month', 'Revenue', 'Onboardings']);

            foreach ($metrics['monthlyTrends'] as $month) {
                fputcsv($handle, [$month['full_label'], $month['revenue'], $month['onboardings']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Plan', 'Active Subscriptions']);

            foreach ($metrics['planDistribution'] as $plan => $count) {
                fputcsv($handle, [$plan, $count]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array{isSuperAdmin: bool, organizationIds: list<int>|null, organizationsQuery: Builder<Organization>, branchesQuery: Builder<Tenant>, staffQuery: Builder<StaffMember>, paymentsQuery: Builder<SubscriptionPayment>, completedPaymentsQuery: Builder<SubscriptionPayment>, activeSubscriptions: Collection<int, OrganizationSubscription>}
     */
    private function buildContext(User $user): array
    {
        $isSuperAdmin = $user->platform_role === PlatformRole::SuperAdmin;
        $organizationIds = $isSuperAdmin
            ? null
            : $user->organizations()->pluck('organizations.id')->all();

        $organizationsQuery = Organization::query();
        $this->applyOrganizationScope($organizationsQuery, $organizationIds);

        $branchesQuery = Tenant::query();
        $this->applyOrganizationScope($branchesQuery, $organizationIds, 'organization_id');

        $staffQuery = StaffMember::query();
        $this->applyOrganizationScope($staffQuery, $organizationIds, 'organization_id');

        $paymentsQuery = SubscriptionPayment::query();
        $this->applyOrganizationScope($paymentsQuery, $organizationIds, 'organization_id');

        $completedPaymentsQuery = (clone $paymentsQuery)
            ->where('status', SubscriptionPaymentStatus::Completed);

        $activeSubscriptionsQuery = OrganizationSubscription::query()
            ->with('plan')
            ->where('status', OrganizationSubscriptionStatus::Active);
        $this->applyOrganizationScope($activeSubscriptionsQuery, $organizationIds, 'organization_id');

        return [
            'isSuperAdmin' => $isSuperAdmin,
            'organizationIds' => $organizationIds,
            'organizationsQuery' => $organizationsQuery,
            'branchesQuery' => $branchesQuery,
            'staffQuery' => $staffQuery,
            'paymentsQuery' => $paymentsQuery,
            'completedPaymentsQuery' => $completedPaymentsQuery,
            'activeSubscriptions' => $activeSubscriptionsQuery->get(),
        ];
    }

    /**
     * @return array{value: float, direction: string}
     */
    private function periodChange(int|float $current, int|float $previous): array
    {
        if ($previous == 0) {
            return [
                'value' => $current > 0 ? 100.0 : 0.0,
                'direction' => $current > 0 ? 'up' : 'flat',
            ];
        }

        $change = (($current - $previous) / $previous) * 100;

        return [
            'value' => abs(round($change, 1)),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat'),
        ];
    }

    /**
     * @param  Collection<int, OrganizationSubscription>  $activeSubscriptions
     * @return list<array{name: string, count: int, percentage: float, color: string}>
     */
    private function planMixPercentages(Collection $activeSubscriptions): array
    {
        $total = max(1, $activeSubscriptions->count());
        $colors = ['bg-primary', 'bg-secondary', 'bg-surface-container', 'bg-tertiary-fixed'];
        $mix = [];
        $index = 0;

        foreach ($this->planDistribution($activeSubscriptions) as $name => $count) {
            $mix[] = [
                'name' => $name,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1),
                'color' => $colors[$index % count($colors)],
            ];
            $index++;
        }

        return $mix;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  list<int>|null  $organizationIds
     */
    private function applyOrganizationScope(Builder $query, ?array $organizationIds, string $column = 'id'): void
    {
        if ($organizationIds === null) {
            return;
        }

        if ($organizationIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn($column, $organizationIds);
    }

    /**
     * @param  Builder<Organization>  $organizationsQuery
     * @param  Builder<SubscriptionPayment>  $completedPaymentsQuery
     * @return list<array{label: string, full_label: string, revenue: float, onboardings: int}>
     */
    private function monthlyTrends(Builder $organizationsQuery, Builder $completedPaymentsQuery): array
    {
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $months[] = [
                'label' => $start->format('M'),
                'full_label' => $start->format('M Y'),
                'revenue' => (float) (clone $completedPaymentsQuery)
                    ->whereBetween('paid_at', [$start, $end])
                    ->sum('amount'),
                'onboardings' => (clone $organizationsQuery)
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ];
        }

        return $months;
    }

    /**
     * @return Collection<string, int>
     */
    private function planDistribution(Collection $activeSubscriptions): Collection
    {
        return $activeSubscriptions
            ->groupBy(fn (OrganizationSubscription $subscription): string => $subscription->plan?->name ?? __('Unassigned'))
            ->map(fn (Collection $group): int => $group->count())
            ->sortDesc();
    }

    /**
     * @param  Builder<SubscriptionPayment>  $completedPaymentsQuery
     * @return Collection<string, int>
     */
    private function paymentMethodBreakdown(Builder $completedPaymentsQuery): Collection
    {
        return (clone $completedPaymentsQuery)
            ->get()
            ->groupBy(fn (SubscriptionPayment $payment): string => $payment->payment_method->value)
            ->map(fn (Collection $group): int => $group->count())
            ->sortDesc();
    }
}
