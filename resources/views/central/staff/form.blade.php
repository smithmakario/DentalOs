@php
    /** @var \App\Models\StaffMember|null $staffMember */
    $staffMember = $staffMember ?? null;
    $selectedBranches = old('branch_ids', $staffMember?->branchAssignments->pluck('tenant_id')->all() ?? []);
@endphp

<x-app-layout>
    <div class="p-8 max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('clinics.staff.index', $organization) }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to Staff') }}
            </a>
            <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ $staffMember ? __('Edit Staff Member') : __('Add Staff Member') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ $organization->name }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-8 shadow-sm">
            <form method="POST" action="{{ $staffMember ? route('clinics.staff.update', [$organization, $staffMember]) : route('clinics.staff.store', $organization) }}" class="space-y-6">
                @csrf
                @if ($staffMember) @method('PATCH') @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-2" for="first_name">{{ __('First Name') }}</label>
                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $staffMember?->first_name) }}" required class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none" />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                    </div>
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-2" for="last_name">{{ __('Last Name') }}</label>
                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $staffMember?->last_name) }}" required class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none" />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-2" for="email">{{ __('Email') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $staffMember?->email) }}" required class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <label class="block font-label-md text-on-surface-variant mb-2" for="password">{{ $staffMember ? __('New Password (optional)') : __('Password') }}</label>
                        <input id="password" name="password" type="password" @unless($staffMember) required @endunless class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <label class="block font-label-md text-on-surface-variant mb-2" for="role">{{ __('Role') }}</label>
                    <select id="role" name="role" required class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role', $staffMember?->role->value) === $role->value)>{{ ucfirst(str_replace('_', ' ', $role->value)) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="border-t border-slate-100 pt-6 space-y-4">
                    <h3 class="font-h3 text-on-surface">{{ __('Branch Access') }}</h3>
                    <label class="flex items-center gap-3">
                        <input type="hidden" name="has_global_branch_access" value="0" />
                        <input type="checkbox" name="has_global_branch_access" value="1" @checked(old('has_global_branch_access', $staffMember?->has_global_branch_access)) class="rounded text-primary focus:ring-primary" />
                        <span class="font-body-md">{{ __('Global access to all branches in this clinic') }}</span>
                    </label>

                    <div id="branch-selection" class="space-y-2">
                        <p class="font-label-md text-on-surface-variant">{{ __('Or assign specific branches:') }}</p>
                        @foreach ($organization->branches as $branch)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}" @checked(in_array($branch->id, $selectedBranches, true)) />
                                <span>{{ $branch->name }}</span>
                            </label>
                        @endforeach
                        <x-input-error :messages="$errors->get('branch_ids')" class="mt-2" />
                    </div>
                </div>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0" />
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $staffMember?->is_active ?? true)) class="rounded text-primary focus:ring-primary" />
                    <span class="font-body-md">{{ __('Active') }}</span>
                </label>

                <button type="submit" class="w-full py-3 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90">
                    {{ $staffMember ? __('Save Changes') : __('Create Staff Member') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
