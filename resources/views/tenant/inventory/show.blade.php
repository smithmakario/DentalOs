<x-tenant-layout>
    <div class="p-8 max-w-5xl mx-auto">
        @include('layouts.partials.flash-messages')

        {{-- Header --}}
        <div class="flex justify-between items-start mb-8">
            <div>
                <a href="{{ route('tenant.inventory.index') }}" class="text-sm text-slate-500 hover:text-primary flex items-center gap-1 mb-3">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>{{ __('Back to Inventory') }}
                </a>
                <h2 class="font-h1 text-h1 text-on-surface">{{ $item->name }}</h2>
                <p class="text-on-surface-variant text-body-md font-body-md mt-1">
                    {{ $item->category->name }} &middot; {{ $item->type->label() }} &middot; {{ $item->unit_measure }}
                    @if ($item->sku)
                        &middot; <span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $item->sku }}</span>
                    @endif
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('tenant.inventory.edit', $item) }}" class="flex items-center gap-2 px-4 py-2 border border-outline rounded-lg text-on-surface hover:bg-surface-container transition-colors text-sm font-label-md">
                    <span class="material-symbols-outlined text-[16px]">edit</span>{{ __('Edit') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Total Stock --}}
            @php $totalStock = $item->batches->where('is_active', true)->where('current_quantity', '>', 0)->sum('current_quantity'); @endphp
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6">
                <div class="text-on-surface-variant text-sm mb-1">{{ __('Total Stock') }}</div>
                <div class="text-3xl font-bold @if($totalStock <= 0) text-error @elseif($totalStock <= $item->min_stock_level) text-amber-600 @else text-on-surface @endif">
                    {{ number_format($totalStock, 2) }}
                </div>
                <div class="text-sm text-on-surface-variant mt-0.5">{{ $item->unit_measure }}</div>
                @if ($item->min_stock_level > 0)
                    <div class="text-xs text-slate-400 mt-2">{{ __('Min level:') }} {{ number_format($item->min_stock_level, 2) }}</div>
                @endif
            </div>

            {{-- Active Batches --}}
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6">
                <div class="text-on-surface-variant text-sm mb-1">{{ __('Active Batches') }}</div>
                <div class="text-3xl font-bold text-on-surface">{{ $item->batches->where('is_active', true)->count() }}</div>
                <div class="text-sm text-on-surface-variant mt-0.5">{{ __('lots in stock') }}</div>
            </div>

            {{-- Nearest Expiry --}}
            @php
                $nearestExpiry = $item->batches->whereNotNull('expiry_date')->where('current_quantity', '>', 0)->sortBy('expiry_date')->first();
            @endphp
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6">
                <div class="text-on-surface-variant text-sm mb-1">{{ __('Nearest Expiry') }}</div>
                @if ($nearestExpiry)
                    <div class="text-xl font-bold @if($nearestExpiry->isExpired()) text-error @elseif($nearestExpiry->isExpiringSoon()) text-amber-600 @else text-on-surface @endif">
                        {{ $nearestExpiry->expiry_date->format('M d, Y') }}
                    </div>
                    <div class="text-sm text-on-surface-variant">{{ __('Lot:') }} {{ $nearestExpiry->lot_number ?? __('N/A') }}</div>
                @else
                    <div class="text-xl font-bold text-slate-400">—</div>
                    <div class="text-sm text-slate-400">{{ __('No expiry tracking') }}</div>
                @endif
            </div>
        </div>

        {{-- Add New Batch / Receive Stock Form --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 mb-6">
            <h3 class="font-title-md text-title-md text-on-surface mb-4">{{ __('Receive New Batch / Lot') }}</h3>
            <form action="{{ route('tenant.inventory.batches.store', $item) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-on-surface-variant mb-1" for="lot_number">{{ __('Lot Number') }}</label>
                    <input id="lot_number" name="lot_number" type="text" placeholder="{{ __('e.g., LOT-2024-001') }}"
                        class="w-full border border-outline rounded-lg px-3 py-2 text-sm text-on-surface bg-surface focus:ring-2 focus:ring-primary outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-on-surface-variant mb-1" for="expiry_date">{{ __('Expiry Date') }}</label>
                    <input id="expiry_date" name="expiry_date" type="date"
                        class="w-full border border-outline rounded-lg px-3 py-2 text-sm text-on-surface bg-surface focus:ring-2 focus:ring-primary outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-on-surface-variant mb-1" for="batch_quantity">{{ __('Quantity Received') }} <span class="text-error">*</span></label>
                    <div class="flex items-center gap-2">
                        <input id="batch_quantity" name="initial_quantity" type="number" step="0.01" min="0.01" required placeholder="0"
                            class="w-full border border-outline rounded-lg px-3 py-2 text-sm text-on-surface bg-surface focus:ring-2 focus:ring-primary outline-none" />
                        <span class="text-sm text-slate-400 whitespace-nowrap">{{ $item->unit_measure }}</span>
                    </div>
                </div>
                <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary font-label-md text-label-md text-sm hover:opacity-90 transition-opacity shadow-md shadow-primary/20">
                    {{ __('Add Batch') }}
                </button>
            </form>
        </div>

        {{-- Batches Table --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="font-title-md text-title-md text-on-surface">{{ __('Batch / Lot Breakdown') }}</h3>
            </div>
            @if ($item->batches->isEmpty())
                <div class="py-10 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl">local_shipping</span>
                    <p class="mt-2 text-sm">{{ __('No batches yet. Add the first stock receipt above.') }}</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="text-left px-6 py-3">{{ __('Lot Number') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Expiry Date') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Initial Qty') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Current Qty') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Status') }}</th>
                            <th class="px-6 py-3">{{ __('Record Usage') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($item->batches as $batch)
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $batch->isExpired() ? 'opacity-60' : '' }}">
                                <td class="px-6 py-4 font-mono text-sm">{{ $batch->lot_number ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if ($batch->expiry_date)
                                        <span class="{{ $batch->isExpired() ? 'text-error font-semibold' : ($batch->isExpiringSoon() ? 'text-amber-600 font-semibold' : 'text-on-surface') }}">
                                            {{ $batch->expiry_date->format('M d, Y') }}
                                        </span>
                                        @if ($batch->isExpired())
                                            <span class="ml-1 text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full">{{ __('Expired') }}</span>
                                        @elseif ($batch->isExpiringSoon())
                                            <span class="ml-1 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">{{ __('Soon') }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">{{ __('No expiry') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ number_format($batch->initial_quantity, 2) }}</td>
                                <td class="px-6 py-4 font-semibold {{ $batch->current_quantity <= 0 ? 'text-error' : 'text-on-surface' }}">
                                    {{ number_format($batch->current_quantity, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if (!$batch->is_active || $batch->current_quantity <= 0)
                                        <span class="px-2 py-0.5 text-xs bg-slate-100 text-slate-500 rounded-full">{{ __('Depleted') }}</span>
                                    @elseif ($batch->isExpired())
                                        <span class="px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded-full">{{ __('Expired') }}</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">{{ __('Active') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($batch->is_active && $batch->current_quantity > 0)
                                        <form action="{{ route('tenant.inventory.transactions.store', $item) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="inventory_batch_id" value="{{ $batch->id }}" />
                                            <select name="type" class="text-xs border border-slate-200 rounded px-2 py-1 focus:ring-primary outline-none">
                                                <option value="consume">{{ __('Consume') }}</option>
                                                <option value="expired">{{ __('Expired') }}</option>
                                                <option value="damaged">{{ __('Damaged') }}</option>
                                                <option value="adjustment">{{ __('Adjust To') }}</option>
                                            </select>
                                            <input type="number" name="quantity" step="0.01" min="0.01" placeholder="Qty"
                                                class="w-20 text-xs border border-slate-200 rounded px-2 py-1 focus:ring-primary outline-none" required />
                                            <button type="submit" class="text-xs px-2 py-1 bg-primary text-on-primary rounded hover:opacity-90">{{ __('Log') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Transaction Log --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="font-title-md text-title-md text-on-surface">{{ __('Transaction History') }}</h3>
            </div>
            @if ($item->transactions->isEmpty())
                <div class="py-10 text-center text-slate-400 text-sm">{{ __('No transactions recorded yet.') }}</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="text-left px-6 py-3">{{ __('Date') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Type') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Lot') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Quantity') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Notes') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Recorded By') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($item->transactions as $tx)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-3 text-slate-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                        @if($tx->type->value === 'restock') bg-green-100 text-green-700
                                        @elseif($tx->type->value === 'consume') bg-blue-100 text-blue-700
                                        @elseif(in_array($tx->type->value, ['expired','damaged'])) bg-red-100 text-red-700
                                        @else bg-slate-100 text-slate-600 @endif">
                                        {{ $tx->type->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 font-mono text-xs">{{ $tx->batch?->lot_number ?? '—' }}</td>
                                <td class="px-6 py-3 font-semibold">{{ number_format($tx->quantity, 2) }}</td>
                                <td class="px-6 py-3 text-slate-500 max-w-xs truncate">{{ $tx->notes ?? '—' }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ $tx->staffMember ? $tx->staffMember->full_name : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-tenant-layout>
