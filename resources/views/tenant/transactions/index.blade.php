<x-tenant-layout>
    <div class="p-8 max-w-7xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-end mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Transactions') }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    @if ($canViewAllBranches)
                        {{ __('Payment history across clinic branches. Filter by branch to drill down.') }}
                    @else
                        {{ __('Payment history for :branch.', ['branch' => tenant('name')]) }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                @can('viewAny', App\Models\Invoice::class)
                    <a href="{{ route('tenant.invoices.index') }}" class="inline-flex items-center gap-2 border border-slate-200 text-on-surface px-5 py-2.5 rounded-lg font-label-md hover:bg-slate-50">
                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                        {{ __('Invoices') }}
                    </a>
                @endcan
                @can('create', App\Models\Invoice::class)
                    <a href="{{ route('tenant.invoices.create') }}" class="inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-lg font-label-md hover:opacity-90">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        {{ __('New Invoice') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($totalAmount, 2) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Total collected (filtered)') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($withProofCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('With payment proof') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ number_format($confirmedCount) }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Confirmed') }}</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <form action="{{ route('tenant.transactions.index') }}" class="flex flex-wrap gap-3 items-end" method="GET">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm w-56 focus:ring-2 focus:ring-primary outline-none" name="search" type="search" value="{{ $search }}" placeholder="{{ __('Search patient, invoice, ref…') }}" />
                    </div>
                    <select class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm" name="payment_method">
                        <option value="">{{ __('All methods') }}</option>
                        @foreach ($paymentMethods as $methodOption)
                            <option value="{{ $methodOption->value }}" @selected($paymentMethod === $methodOption->value)>{{ $methodOption->label() }}</option>
                        @endforeach
                    </select>
                    @if ($canViewAllBranches && $branches->isNotEmpty())
                        <select class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm" name="branch">
                            <option value="">{{ __('All branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->getTenantKey() }}" @selected((string) $selectedBranch === (string) $branch->getTenantKey())>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-medium hover:opacity-90" type="submit">{{ __('Filter') }}</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Date') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Patient') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Invoice') }}</th>
                            @if ($canViewAllBranches)
                                <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Branch') }}</th>
                            @endif
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Method') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant text-right">{{ __('Amount') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Proof') }}</th>
                            <th class="px-6 py-4 text-label-sm text-on-surface-variant">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-body-sm text-on-surface-variant">{{ $transaction['paid_at_label'] }}</td>
                                <td class="px-6 py-4 text-body-md text-on-surface">{{ $transaction['patient_name'] }}</td>
                                <td class="px-6 py-4">
                                    @if ($transaction['invoice_url'])
                                        <a class="font-label-md text-primary hover:underline" href="{{ $transaction['invoice_url'] }}">{{ $transaction['invoice_number'] }}</a>
                                    @else
                                        {{ $transaction['invoice_number'] }}
                                    @endif
                                    <div class="text-xs text-slate-500">{{ $transaction['payment_number'] }}</div>
                                </td>
                                @if ($canViewAllBranches)
                                    <td class="px-6 py-4 text-body-sm text-on-surface-variant">{{ $transaction['branch_name'] }}</td>
                                @endif
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $transaction['payment_method'] }}</span>
                                    @if ($transaction['payment_reference'])
                                        <div class="text-xs text-slate-500 mt-1">{{ $transaction['payment_reference'] }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-label-md text-on-surface">{{ number_format($transaction['amount'], 2) }}</td>
                                <td class="px-6 py-4">
                                    @if ($transaction['proof_url'])
                                        <a href="{{ $transaction['proof_url'] }}" target="_blank" class="text-primary text-sm hover:underline">{{ __('View') }}</a>
                                    @else
                                        <span class="text-slate-400 text-sm">{{ __('None') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($transaction['is_confirmed'])
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">{{ __('Confirmed') }}</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">{{ __('Recorded') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-on-surface-variant" colspan="{{ $canViewAllBranches ? 8 : 7 }}">{{ __('No transactions yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div class="p-6 border-t border-slate-100">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
</x-tenant-layout>
