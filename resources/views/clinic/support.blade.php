<x-clinic-layout>
    <div class="p-8 max-w-3xl mx-auto">
        <h2 class="font-h1 text-on-surface mb-2">{{ __('Contact Support') }}</h2>
        <p class="text-on-surface-variant mb-8">
            {{ __('Need help with :clinic? Reach the DentalOs team and include your clinic name.', ['clinic' => $organization->name]) }}
        </p>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 space-y-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-bold">{{ __('Support email') }}</p>
                <a href="mailto:{{ $supportEmail }}?subject={{ urlencode(__('Support request — :clinic', ['clinic' => $organization->name])) }}" class="text-lg text-primary hover:underline">
                    {{ $supportEmail }}
                </a>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-500 font-bold">{{ __('From') }}</p>
                <p class="text-on-surface">{{ $supportName }}</p>
            </div>
            <p class="text-sm text-slate-500">
                {{ __('Include your clinic slug (:slug) and a short description of the issue.', ['slug' => $organization->slug]) }}
            </p>
        </div>
    </div>
</x-clinic-layout>
