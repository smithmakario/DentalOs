<x-app-layout>
    <div class="p-8 max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('subscription-plans.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to Plans') }}
            </a>
            <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Create Subscription Plan') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ __('Define a new plan for Professional or Enterprise organizations.') }}</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
            @include('central.subscription-plans.form', [
                'plan' => $plan,
                'organizationTypes' => $organizationTypes,
                'action' => route('subscription-plans.store'),
                'submitLabel' => __('Create Plan'),
            ])
        </div>
    </div>
</x-app-layout>
