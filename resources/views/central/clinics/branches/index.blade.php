<x-app-layout>
    <div class="p-8 max-w-5xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('clinics.edit', $organization) }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to Clinic') }}
            </a>
            <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Manage Branches') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ $organization->name }}</p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-h3 text-on-surface">{{ __('Branches') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Branch') }}</th>
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Domain') }}</th>
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Portal') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($branches as $branch)
                                @php $domain = $branch->domains->first(); @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">{{ $branch->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $branch->slug }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-body-md text-on-surface">{{ $domain?->domain ?? '—' }}</td>
                                    <td class="px-6 py-4">
                                        @if ($branch->is_active)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                                {{ __('ACTIVE') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                                {{ __('INACTIVE') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if ($domain)
                                            <a href="http://{{ $domain->domain }}:{{ request()->getPort() ?: 8000 }}/staff/login" target="_blank"
                                                class="inline-flex items-center gap-1 text-primary font-label-md hover:underline">
                                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                                                {{ __('Open') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-body-md text-on-surface-variant">
                                        {{ __('No branches yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 h-fit">
                <h3 class="font-h3 text-on-surface mb-6">{{ __('Add Branch') }}</h3>
                <form method="POST" action="{{ route('clinics.branches.store', $organization) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="branch_name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Branch Name') }}</label>
                        <input id="branch_name" name="branch_name" type="text" value="{{ old('branch_name') }}" required
                            class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none font-body-md @error('branch_name') border-error @enderror" />
                        <x-input-error :messages="$errors->get('branch_name')" class="mt-2" />
                    </div>

                    <div>
                        <label for="branch_slug" class="block font-label-md text-on-surface-variant mb-2">{{ __('Branch Slug') }}</label>
                        <input id="branch_slug" name="branch_slug" type="text" value="{{ old('branch_slug') }}" required
                            class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none font-body-md @error('branch_slug') border-error @enderror" />
                        <x-input-error :messages="$errors->get('branch_slug')" class="mt-2" />
                    </div>

                    <div>
                        <label for="domain" class="block font-label-md text-on-surface-variant mb-2">{{ __('Domain') }}</label>
                        <input id="domain" name="domain" type="text" value="{{ old('domain') }}" required placeholder="uptown.localhost"
                            class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none font-body-md @error('domain') border-error @enderror" />
                        <p class="mt-1 text-body-sm text-outline">
                            {{ __('Hostname only — do not include http:// or port numbers.') }}
                        </p>
                        <x-input-error :messages="$errors->get('domain')" class="mt-2" />
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                        {{ __('Create Branch') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
