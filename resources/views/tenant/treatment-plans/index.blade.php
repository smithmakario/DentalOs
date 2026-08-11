<x-tenant-layout>
    <div class="p-8 max-w-7xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Treatment Plans') }}</h2>
                <p class="font-body-md text-on-surface-variant">
                    {{ __('Plan and track clinical procedures for patients at :branch.', ['branch' => tenant('name')]) }}
                </p>
            </div>
            @can('create', App\Models\TreatmentPlan::class)
                <a href="{{ route('tenant.treatment-plans.create') }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90 transition-opacity shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    {{ __('New Treatment Plan') }}
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($activeCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Active Plans') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($draftCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Draft Plans') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($completedCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Completed Plans') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <form action="{{ route('tenant.treatment-plans.index') }}" class="p-6 border-b border-slate-100 flex flex-wrap gap-4 items-center bg-slate-50/50" method="GET">
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                    </span>
                    <input class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm w-64 focus:ring-2 focus:ring-primary outline-none" name="search" type="text" value="{{ $search }}" placeholder="{{ __('Search patient or plan...') }}" />
                </div>
                <select class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none" name="status" onchange="this.form.submit()">
                    <option value="">{{ __('All Statuses') }}</option>
                    @foreach (\App\Enums\TreatmentPlanStatus::cases() as $planStatus)
                        <option value="{{ $planStatus->value }}" @selected($status === $planStatus->value)>{{ ucfirst($planStatus->value) }}</option>
                    @endforeach
                </select>
                <button class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-label-md" type="submit">{{ __('Filter') }}</button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Plan') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Patient') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Provider') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Estimate') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($treatmentPlans as $plan)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <a class="font-medium text-primary hover:underline" href="{{ route('tenant.treatment-plans.show', $plan) }}">{{ $plan->title }}</a>
                                    <p class="text-xs text-slate-500">{{ trans_choice(':count option|:count options', $plan->options_count, ['count' => $plan->options_count]) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <a class="text-on-surface hover:text-primary" href="{{ route('tenant.patients.show', $plan->patient) }}">{{ $plan->patient->full_name }}</a>
                                </td>
                                <td class="px-6 py-4 text-body-md text-on-surface">{{ $plan->provider->full_name }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ ucfirst($plan->status->value) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-label-md text-on-surface">{{ number_format($plan->estimated_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-on-surface-variant" colspan="5">{{ __('No treatment plans yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($treatmentPlans->hasPages())
                <div class="p-6 border-t border-slate-100">{{ $treatmentPlans->links() }}</div>
            @endif
        </div>
    </div>
</x-tenant-layout>
