@php
    $inputClass = 'w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none font-body-md';
@endphp

<x-clinic-layout>
    <div
        class="p-8 max-w-4xl mx-auto"
        x-data="{
            open: {{ $errors->any() ? 'true' : 'false' }},
            name: @js(old('branch_name', '')),
            slug: @js(old('branch_slug', '')),
            appHost: @js($appHost),
            organizationSlug: @js($organization->slug),
            slugify(value) {
                return String(value || '')
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .slice(0, 63);
            },
            syncSlug() {
                this.slug = this.slugify(this.name);
            },
        }"
    >
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Clinic Branches') }}</h2>
                <p class="font-body-md text-on-surface-variant">
                    {{ __('Manage every branch for :clinic from the clinic portal.', ['clinic' => $organization->name]) }}
                </p>
            </div>
            @if ($canAddBranch)
                <button type="button" @click="open = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    {{ __('Add Branch') }}
                </button>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <ul class="divide-y divide-slate-100">
                @foreach ($branches as $branch)
                    <li class="px-6 py-4 flex items-center justify-between gap-3">
                        <div>
                            <div class="font-medium text-on-surface flex items-center gap-2">
                                {{ $branch->name }}
                                @if ($organization->isAdminBranch($branch))
                                    <span class="text-[10px] uppercase tracking-wide font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100">{{ __('Admin / HQ') }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-on-surface-variant font-mono mt-1">
                                {{ $appHost }}/{{ $organization->slug }}/{{ $branch->slug }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($branch->is_active)
                                <span class="text-xs font-bold text-green-700">{{ __('Active') }}</span>
                            @else
                                <span class="text-xs font-bold text-red-700">{{ __('Inactive') }}</span>
                            @endif
                            <a href="{{ \App\Support\TenantPortal::loginUrl($branch) }}" class="text-sm text-primary hover:underline">{{ __('Branch staff login') }}</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div
            x-cloak
            x-show="open"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40"
            @keydown.escape.window="open = false"
        >
            <div class="w-full max-w-lg bg-white rounded-xl shadow-xl border border-slate-200 p-6" @click.outside="open = false">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Add Branch') }}</h3>
                <form method="POST" action="{{ route('clinic.branches.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="branch_name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Branch name') }}</label>
                        <input id="branch_name" name="branch_name" type="text" x-model="name" @input="syncSlug" required class="{{ $inputClass }}" />
                        <x-input-error :messages="$errors->get('branch_name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="branch_slug" class="block font-label-md text-on-surface-variant mb-2">{{ __('Branch slug') }}</label>
                        <input id="branch_slug" name="branch_slug" type="text" x-model="slug" required class="{{ $inputClass }}" />
                        <p class="mt-1 text-xs text-on-surface-variant" x-show="slug" x-text="`${appHost}/${organizationSlug}/${slug}`"></p>
                        <x-input-error :messages="$errors->get('branch_slug')" class="mt-2" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2.5 border border-outline-variant rounded-lg font-label-md">{{ __('Cancel') }}</button>
                        <button type="submit" class="px-4 py-2.5 bg-primary text-on-primary rounded-lg font-label-md">{{ __('Create branch') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-clinic-layout>
