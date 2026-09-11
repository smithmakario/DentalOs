<x-tenant-layout>
    <div class="p-8 max-w-7xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Inventory Management') }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ __('Track stock levels, batches, expiry dates, and usage for :branch.', ['branch' => tenant('name')]) }}
                </p>
            </div>
            <a href="{{ route('tenant.inventory.create') }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md text-label-md hover:opacity-90 transition-opacity shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-[18px]">add_box</span>
                {{ __('Add Item') }}
            </a>
        </div>

        {{-- Alert Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="{{ route('tenant.inventory.index', ['filter' => 'low_stock']) }}" class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-lg material-symbols-outlined">warning</span>
                    @if ($lowStockCount > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-700 rounded-full">Action Needed</span>
                    @endif
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ $lowStockCount }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Low Stock Items') }}</div>
            </a>
            <a href="{{ route('tenant.inventory.index', ['filter' => 'out_of_stock']) }}" class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-error-container text-error rounded-lg material-symbols-outlined">inventory_2</span>
                    @if ($outOfStockCount > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded-full">Critical</span>
                    @endif
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ $outOfStockCount }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Out of Stock') }}</div>
            </a>
            <a href="{{ route('tenant.inventory.index', ['filter' => 'expiring_soon']) }}" class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-orange-50 text-orange-600 rounded-lg material-symbols-outlined">event_upcoming</span>
                    @if ($expiringSoonCount > 0)
                        <span class="px-2 py-0.5 text-xs font-semibold bg-orange-100 text-orange-700 rounded-full">Review</span>
                    @endif
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ $expiringSoonCount }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Expiring Within 30 Days') }}</div>
            </a>
        </div>

        {{-- Items Table --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-100 flex flex-wrap gap-4 items-center justify-between bg-slate-50/50">
                <form action="{{ route('tenant.inventory.index') }}" class="flex flex-wrap gap-3 items-center" method="GET">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </span>
                        <input
                            class="pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm w-56 focus:ring-2 focus:ring-primary focus:border-transparent outline-none"
                            name="search"
                            placeholder="{{ __('Search items…') }}"
                            value="{{ $search }}"
                            type="search"
                        />
                    </div>
                    <select name="filter" class="py-2 px-3 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none" onchange="this.form.submit()">
                        <option value="" @selected($filterType === '')>{{ __('All Items') }}</option>
                        <option value="low_stock" @selected($filterType === 'low_stock')>{{ __('Low Stock') }}</option>
                        <option value="out_of_stock" @selected($filterType === 'out_of_stock')>{{ __('Out of Stock') }}</option>
                        <option value="expiring_soon" @selected($filterType === 'expiring_soon')>{{ __('Expiring Soon') }}</option>
                    </select>
                    @if ($search || $filterType)
                        <a href="{{ route('tenant.inventory.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">close</span>{{ __('Clear') }}
                        </a>
                    @endif
                </form>
            </div>

            @if ($items->isEmpty())
                <div class="py-16 text-center">
                    <span class="material-symbols-outlined text-5xl text-slate-300">inventory_2</span>
                    <p class="mt-4 text-slate-500 font-medium">{{ __('No inventory items found.') }}</p>
                    <a href="{{ route('tenant.inventory.create') }}" class="mt-4 inline-flex items-center gap-1 text-primary font-medium text-sm hover:underline">
                        <span class="material-symbols-outlined text-[16px]">add</span>{{ __('Add your first item') }}
                    </a>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="text-left px-6 py-3">{{ __('Item') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Category / Type') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Total Stock') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Batches') }}</th>
                            <th class="text-left px-6 py-3">{{ __('Status') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($items as $item)
                            @php
                                $stock = (float) $item->total_stock;
                                $isLow = $stock > 0 && $stock <= (float) $item->min_stock_level;
                                $isOut = $stock <= 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-on-surface">{{ $item->name }}</div>
                                    @if ($item->sku)
                                        <div class="text-xs text-slate-400 mt-0.5">SKU: {{ $item->sku }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-on-surface-variant">{{ $item->category->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $item->type->label() }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold {{ $isOut ? 'text-error' : ($isLow ? 'text-amber-600' : 'text-on-surface') }}">
                                        {{ number_format($stock, 2) }}
                                    </span>
                                    <span class="text-xs text-slate-400 ml-1">{{ $item->unit_measure }}</span>
                                    @if ($item->min_stock_level > 0)
                                        <div class="text-xs text-slate-400">Min: {{ number_format($item->min_stock_level, 2) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant">
                                    {{ $item->activeBatches->count() }} {{ __('active') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($isOut)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded-full">
                                            <span class="material-symbols-outlined text-[12px]">block</span>{{ __('Out of Stock') }}
                                        </span>
                                    @elseif ($isLow)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-700 rounded-full">
                                            <span class="material-symbols-outlined text-[12px]">warning</span>{{ __('Low Stock') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                                            <span class="material-symbols-outlined text-[12px]">check_circle</span>{{ __('In Stock') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('tenant.inventory.show', $item) }}" class="text-primary hover:underline text-sm font-medium">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($items->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $items->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-tenant-layout>
