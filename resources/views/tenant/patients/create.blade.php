<x-tenant-layout>
    <div class="p-8 max-w-4xl mx-auto">
        <div class="mb-8">
            <a class="inline-flex items-center gap-1 text-body-md text-primary hover:underline mb-4" href="{{ route('tenant.patients.index') }}">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to patients') }}
            </a>
            <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Add Patient') }}</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">{{ __('Create a new patient record for this branch.') }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8">
            <form action="{{ route('tenant.patients.store') }}" method="POST">
                @csrf
                @include('tenant.patients._form', ['patient' => $patient])

                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                    <a class="px-6 py-2.5 border border-slate-200 rounded-lg text-body-md text-slate-600 hover:bg-slate-50" href="{{ route('tenant.patients.index') }}">
                        {{ __('Cancel') }}
                    </a>
                    <button class="px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity" type="submit">
                        {{ __('Create Patient') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-tenant-layout>
