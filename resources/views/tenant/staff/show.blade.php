<x-tenant-layout>
    <div class="p-8 max-w-5xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="mb-8 flex flex-col md:flex-row md:items-start md:justify-between gap-6">
            <div>
                <a class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4" href="{{ route('tenant.staff.index') }}">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    {{ __('Back to staff') }}
                </a>
                <div class="flex items-center gap-4">
                    <img alt="{{ $member->full_name }}" class="w-20 h-20 rounded-2xl object-cover border border-outline-variant shadow-sm" src="{{ $member->avatarUrl() }}" />
                    <div>
                        <h2 class="font-h1 text-h1 text-on-surface mb-1">{{ $member->full_name }}</h2>
                        <p class="font-body-md text-on-surface-variant">{{ $member->role->label() }}</p>
                        @if ($member->specialization)
                            <p class="font-body-sm text-primary mt-1">{{ $member->specialization }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                @can('update', $member)
                    <a class="px-6 py-2.5 border border-outline-variant rounded-lg font-label-md hover:bg-slate-50" href="{{ route('tenant.staff.edit', $member) }}">{{ __('Edit Profile') }}</a>
                @endcan
                @can('delete', $member)
                    <form action="{{ route('tenant.staff.destroy', $member) }}" method="POST" onsubmit="return confirm(@js(__('Deactivate this staff member?')))">
                        @csrf
                        @method('DELETE')
                        <button class="px-6 py-2.5 border border-error/30 text-error rounded-lg font-label-md hover:bg-error/5" type="submit">{{ __('Deactivate') }}</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm">
                <p class="text-body-sm text-on-surface-variant mb-1">{{ __('Email') }}</p>
                <p class="font-label-md text-on-surface">{{ $member->email }}</p>
            </div>
            <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm">
                <p class="text-body-sm text-on-surface-variant mb-1">{{ __('Phone') }}</p>
                <p class="font-label-md text-on-surface">{{ $member->phone ?: '—' }}</p>
            </div>
            <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm">
                <p class="text-body-sm text-on-surface-variant mb-1">{{ __('Status') }}</p>
                @if ($member->is_active)
                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">{{ __('ACTIVE') }}</span>
                @else
                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ __('INACTIVE') }}</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm">
                <h3 class="font-h3 text-h3 text-on-surface mb-4">{{ __('Professional Details') }}</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-body-sm text-on-surface-variant">{{ __('Specialty') }}</dt>
                        <dd class="font-body-md text-on-surface">{{ $member->specialization ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-body-sm text-on-surface-variant">{{ __('License Number') }}</dt>
                        <dd class="font-body-md text-on-surface">{{ $member->license_number ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-body-sm text-on-surface-variant">{{ __('Years of Experience') }}</dt>
                        <dd class="font-body-md text-on-surface">
                            {{ $member->years_of_experience !== null ? $member->years_of_experience.' '.__('years') : '—' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm">
                <h3 class="font-h3 text-h3 text-on-surface mb-4">{{ __('Activity') }}</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-body-sm text-on-surface-variant">{{ __('Appointments as Provider') }}</dt>
                        <dd class="font-body-md text-on-surface">{{ number_format($member->appointments_as_provider_count) }}</dd>
                    </div>
                    <div>
                        <dt class="text-body-sm text-on-surface-variant">{{ __('Treatment Plans') }}</dt>
                        <dd class="font-body-md text-on-surface">{{ number_format($member->treatment_plans_count) }}</dd>
                    </div>
                    <div>
                        <dt class="text-body-sm text-on-surface-variant">{{ __('Permissions') }}</dt>
                        <dd class="flex flex-wrap gap-2 mt-2">
                            @foreach (\App\Support\StaffRolePermissions::permissionLabels($member->role) as $permission)
                                <span class="inline-flex px-2 py-1 rounded-md text-xs bg-surface-container-low text-on-surface-variant">{{ $permission }}</span>
                            @endforeach
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-tenant-layout>
