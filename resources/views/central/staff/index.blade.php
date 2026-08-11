<x-app-layout>
    <div class="p-8 max-w-5xl mx-auto">
        <div class="mb-8">
            <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Staff Management') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ __('Assign roles and branch access for clinic staff.') }}</p>
        </div>

        <div class="grid gap-4">
            @foreach ($organizations as $organization)
                <a href="{{ route('clinics.staff.index', $organization) }}" class="block bg-white border border-slate-200 rounded-xl p-6 hover:border-primary transition-colors shadow-sm">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-h3 text-on-surface">{{ $organization->name }}</h3>
                            <p class="text-body-sm text-outline">{{ trans_choice(':count staff member|:count staff members', $organization->staff_members_count, ['count' => $organization->staff_members_count]) }}</p>
                        </div>
                        <span class="material-symbols-outlined text-primary">arrow_forward</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
