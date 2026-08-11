<x-tenant-layout>
    <div class="p-8 max-w-5xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="mb-8">
            <a class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4" href="{{ route('tenant.treatment-plans.index') }}">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to treatment plans') }}
            </a>
            <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('New Treatment Plan') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ __('Create a proposed course of treatment for a patient.') }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8">
            <form method="POST" action="{{ route('tenant.treatment-plans.store') }}" class="space-y-6" novalidate>
                @csrf
                @include('tenant.treatment-plans._form', ['treatmentPlan' => $treatmentPlan, 'patients' => $patients, 'providers' => $providers, 'clinicServices' => $clinicServices])

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a class="px-6 py-2.5 border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-slate-50" href="{{ route('tenant.treatment-plans.index') }}">{{ __('Cancel') }}</a>
                    <button class="px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90" type="submit">{{ __('Create Plan') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-tenant-layout>
