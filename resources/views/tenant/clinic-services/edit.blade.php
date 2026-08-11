<x-tenant-layout>
    <div class="p-8 max-w-3xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="mb-8">
            <a class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4" href="{{ route('tenant.clinic-services.index') }}">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to services') }}
            </a>
            <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Edit Service') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ $service->name }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8 mb-4">
            <form method="POST" action="{{ route('tenant.clinic-services.update', $service) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('tenant.clinic-services._form', ['service' => $service])
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a class="px-6 py-2.5 border border-outline-variant rounded-lg font-label-md" href="{{ route('tenant.clinic-services.index') }}">{{ __('Cancel') }}</a>
                    <button class="px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90" type="submit">{{ __('Save Changes') }}</button>
                </div>
            </form>
        </div>

        @can('delete', $service)
            <form method="POST" action="{{ route('tenant.clinic-services.destroy', $service) }}" onsubmit="return confirm(@js(__('Delete this service?')))">
                @csrf
                @method('DELETE')
                <button class="text-error font-label-md hover:underline" type="submit">{{ __('Delete Service') }}</button>
            </form>
        @endcan
    </div>
</x-tenant-layout>
