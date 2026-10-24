@props([
    'action',
    'branches',
    'selectedBranch' => null,
    'extra' => [],
])

<form method="GET" action="{{ $action }}" class="flex flex-wrap gap-3 items-end">
    @foreach ($extra as $name => $value)
        @if ($value !== null && $value !== '')
            <input type="hidden" name="{{ $name }}" value="{{ $value }}" />
        @endif
    @endforeach

    <div>
        <label for="branch" class="block text-xs font-semibold text-on-surface-variant mb-1">{{ __('Branch') }}</label>
        <select id="branch" name="branch" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm min-w-[12rem]" onchange="this.form.submit()">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) $selectedBranch === (string) $branch->id)>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{ $slot }}

    <button type="submit" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50">
        {{ __('Apply') }}
    </button>
</form>
