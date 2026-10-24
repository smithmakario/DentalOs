@props([
    'path' => '/dashboard',
    'label' => null,
])

@php
    $branches = $clinicBranches ?? collect();
    $org = $organization ?? null;
    $canChoose = ($staffCanManageClinic ?? false) && $branches->count() > 1;
    $label ??= __('Working branch');
@endphp

@if ($canChoose)
    <div {{ $attributes->class(['rounded-xl border border-slate-200 bg-slate-50 p-4']) }}>
        <label for="working-branch" class="block text-sm font-medium text-slate-700 mb-1">{{ $label }}</label>
        <p class="text-xs text-slate-500 mb-2">{{ __('Primary admins can create and manage records on any branch in this clinic.') }}</p>
        <select
            id="working-branch"
            class="w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary"
            onchange="if (this.value) { window.location = this.value; }"
        >
            @foreach ($branches as $branch)
                <option
                    value="{{ \App\Support\TenantPortal::url($branch, $path) }}"
                    @selected((string) $branch->getTenantKey() === (string) tenant('id'))
                >
                    {{ $branch->name }}
                    @if ($org?->isAdminBranch($branch))
                        ({{ __('HQ') }})
                    @endif
                </option>
            @endforeach
        </select>
    </div>
@endif
