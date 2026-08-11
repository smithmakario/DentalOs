<x-tenant-layout>
    <div class="p-8 max-w-4xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="mb-8">
            <a class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4" href="{{ route('tenant.staff.show', $member) }}">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to profile') }}
            </a>
            <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Edit Staff Member') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ $member->full_name }}</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8">
            <form action="{{ route('tenant.staff.update', $member) }}" class="space-y-6" enctype="multipart/form-data" method="POST">
                @csrf
                @method('PATCH')
                @include('tenant.staff._form', ['member' => $member, 'roles' => $roles])

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <a class="px-6 py-2.5 border border-outline-variant rounded-lg font-label-md" href="{{ route('tenant.staff.show', $member) }}">{{ __('Cancel') }}</a>
                    <button class="px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90" type="submit">{{ __('Save Changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-tenant-layout>
