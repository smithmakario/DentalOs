<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Registration Requests') }}</h2>
                <p class="font-body-md text-on-surface-variant">
                    {{ __('Review clinic applications submitted from Get Started.') }}
                </p>
            </div>
            <div class="text-body-sm text-on-surface-variant">
                {{ __(':count pending', ['count' => number_format($pendingCount)]) }}
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm mb-6">
            <form action="{{ route('registration-requests.index') }}" class="p-6 border-b border-slate-100 flex flex-wrap gap-4 items-end bg-slate-50/50" method="GET">
                <div>
                    <label class="block font-label-sm text-on-surface-variant mb-1" for="status">{{ __('Status') }}</label>
                    <select class="pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-sm" id="status" name="status">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (App\Enums\RegistrationRequestStatus::cases() as $statusOption)
                            <option value="{{ $statusOption->value }}" @selected($filters['status'] === $statusOption->value)>
                                {{ $statusOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="px-4 py-2 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90" type="submit">{{ __('Filter') }}</button>
                @if ($filters['status'] !== '')
                    <a class="text-sm text-primary hover:underline" href="{{ route('registration-requests.index') }}">{{ __('Clear') }}</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Organization') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Contact') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Location') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Submitted') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $registrationRequest)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-body-md text-on-surface">{{ $registrationRequest->name }}</p>
                                    <p class="text-body-sm text-outline">{{ $registrationRequest->government_approval }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-body-md text-on-surface">{{ $registrationRequest->contact_person }}</p>
                                    <p class="text-body-sm text-outline">{{ $registrationRequest->email }}</p>
                                    <p class="text-body-sm text-outline">{{ $registrationRequest->phone }}</p>
                                </td>
                                <td class="px-6 py-4 text-body-md text-on-surface">{{ $registrationRequest->location }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold {{ $registrationRequest->status->colorClass() }}">
                                        {{ $registrationRequest->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-body-sm text-on-surface whitespace-nowrap">
                                    {{ $registrationRequest->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a class="font-label-md text-primary hover:underline" href="{{ route('registration-requests.show', $registrationRequest) }}">
                                        {{ __('Review') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-body-md text-on-surface-variant" colspan="6">
                                    {{ __('No registration requests found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="p-6 border-t border-slate-100">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
