<x-tenant-layout>
    <div class="p-8 max-w-6xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Clinic Services') }}</h2>
                <p class="font-body-md text-on-surface-variant">{{ __('Manage treatments and procedures your branch offers, with clinical codes and pricing.') }}</p>
            </div>
            @can('create', App\Models\ClinicService::class)
                <a href="{{ route('tenant.clinic-services.create') }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    {{ __('Add Service') }}
                </a>
            @endcan
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <form action="{{ route('tenant.clinic-services.index') }}" class="p-6 border-b border-slate-100 flex flex-wrap gap-4 bg-slate-50/50" method="GET">
                <input class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm w-64" name="search" type="text" value="{{ $search }}" placeholder="{{ __('Search name or code...') }}" />
                <select class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm" name="category" onchange="this.form.submit()">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
                <button class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm" type="submit">{{ __('Filter') }}</button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Service') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Category') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Price') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($services as $service)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-on-surface">{{ $service->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $service->code }}</div>
                                </td>
                                <td class="px-6 py-4 text-body-md">{{ $service->category }}</td>
                                <td class="px-6 py-4 text-right font-label-md">{{ \App\Support\Money::naira($service->price) }}</td>
                                <td class="px-6 py-4">
                                    @if ($service->is_active)
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">{{ __('ACTIVE') }}</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ __('INACTIVE') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('update', $service)
                                        <a class="text-primary font-label-md hover:underline" href="{{ route('tenant.clinic-services.edit', $service) }}">{{ __('Edit') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-on-surface-variant" colspan="5">{{ __('No services configured yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($services->hasPages())
                <div class="p-6 border-t border-slate-100">{{ $services->links() }}</div>
            @endif
        </div>
    </div>
</x-tenant-layout>
