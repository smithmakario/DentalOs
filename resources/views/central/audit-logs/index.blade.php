<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Audit Log') }}</h2>
                <p class="font-body-md text-on-surface-variant">{{ __('Review administrative actions across the platform.') }}</p>
            </div>
            <div class="text-body-sm text-on-surface-variant">
                {{ __(':count events', ['count' => number_format($totalLogs)]) }}
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm mb-6">
            <form action="{{ route('audit-logs.index') }}" class="p-6 border-b border-slate-100 flex flex-wrap gap-4 items-end bg-slate-50/50" method="GET">
                <div>
                    <label class="block font-label-sm text-on-surface-variant mb-1" for="action">{{ __('Action') }}</label>
                    <select class="pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-sm" id="action" name="action">
                        <option value="">{{ __('All Actions') }}</option>
                        @foreach ($actions as $actionOption)
                            <option value="{{ $actionOption->value }}" @selected($filters['action'] === $actionOption->value)>
                                {{ $actionOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($organizations->isNotEmpty())
                    <div>
                        <label class="block font-label-sm text-on-surface-variant mb-1" for="organization_id">{{ __('Clinic') }}</label>
                        <select class="pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-sm" id="organization_id" name="organization_id">
                            <option value="">{{ __('All Clinics') }}</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}" @selected($filters['organization_id'] === $organization->id)>
                                    {{ $organization->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block font-label-sm text-on-surface-variant mb-1" for="from">{{ __('From') }}</label>
                    <input class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm" id="from" name="from" type="date" value="{{ $filters['from'] }}" />
                </div>
                <div>
                    <label class="block font-label-sm text-on-surface-variant mb-1" for="to">{{ __('To') }}</label>
                    <input class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm" id="to" name="to" type="date" value="{{ $filters['to'] }}" />
                </div>
                <button class="px-4 py-2 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90" type="submit">{{ __('Filter') }}</button>
                @if ($filters['action'] || $filters['organization_id'] || $filters['from'] || $filters['to'])
                    <a class="text-sm text-primary hover:underline" href="{{ route('audit-logs.index') }}">{{ __('Clear') }}</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('When') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('User') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Action') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Description') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Clinic') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-body-sm text-on-surface whitespace-nowrap">
                                    {{ $log->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-body-md text-on-surface">{{ $log->user_name ?? __('System') }}</p>
                                    <p class="text-body-sm text-outline">{{ $log->user_email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-primary-fixed text-primary rounded-md text-xs font-bold">
                                        {{ $log->action->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-body-md text-on-surface max-w-md">{{ $log->description }}</td>
                                <td class="px-6 py-4 text-body-sm text-outline">{{ $log->organization?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-body-md text-on-surface-variant" colspan="5">
                                    {{ __('No audit events recorded yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="p-6 border-t border-slate-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
