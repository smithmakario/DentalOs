@php
    use App\Enums\OrganizationRole;
    use App\Enums\OrganizationType;

    $avatarClasses = [
        'bg-blue-100 text-blue-600',
        'bg-teal-100 text-teal-600',
        'bg-orange-100 text-orange-600',
        'bg-indigo-100 text-indigo-600',
    ];

    $tierForOrganization = function ($organization): array {
        $planName = $organization->activeSubscription?->plan?->name;

        if ($planName !== null) {
            return match ($planName) {
                'Enterprise' => [
                    'label' => __('Enterprise'),
                    'icon' => 'diamond',
                    'filled' => true,
                    'iconClass' => 'text-blue-500',
                ],
                'Professional' => [
                    'label' => __('Professional'),
                    'icon' => 'workspace_premium',
                    'filled' => false,
                    'iconClass' => 'text-slate-400',
                ],
                default => [
                    'label' => $planName,
                    'icon' => 'verified',
                    'filled' => false,
                    'iconClass' => 'text-slate-400',
                ],
            };
        }

        return match ($organization->type) {
            OrganizationType::Dso => [
                'label' => __('Enterprise'),
                'icon' => 'diamond',
                'filled' => true,
                'iconClass' => 'text-blue-500',
            ],
            OrganizationType::Single => $organization->branches_count <= 1
                ? [
                    'label' => __('Standard'),
                    'icon' => 'verified',
                    'filled' => false,
                    'iconClass' => 'text-slate-400',
                ]
                : [
                    'label' => __('Professional'),
                    'icon' => 'workspace_premium',
                    'filled' => false,
                    'iconClass' => 'text-slate-400',
                ],
        };
    };

    $initialsFor = fn (string $name): string => collect(explode(' ', $name))
        ->filter()
        ->take(2)
        ->map(fn (string $word): string => strtoupper(substr($word, 0, 1)))
        ->join('');

    $primaryAdminFor = fn ($organization) => $organization->users->first(
        fn ($user) => in_array($user->pivot->role, [OrganizationRole::Owner, OrganizationRole::Admin], true)
    );
@endphp

<x-app-layout>
    <div class="p-8 max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ __('Clinic Management') }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ __('Oversee and manage all active dental networks and individual practices on the platform.') }}
                </p>
            </div>
            @can('create', App\Models\Organization::class)
                <a href="{{ route('clinics.create') }}" class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md text-label-md hover:opacity-90 transition-opacity shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    {{ __('Onboard New Clinic') }}
                </a>
            @endcan
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">
                {{ session('status') }}
            </div>
        @endif

        <!-- Bento Stats (Overview) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-lg material-symbols-outlined">corporate_fare</span>
                    <span class="text-green-600 font-label-sm text-label-sm">+12%</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($totalClinics) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Total Clinics') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-secondary-container/20 text-secondary rounded-lg material-symbols-outlined">account_tree</span>
                    <span class="text-green-600 font-label-sm text-label-sm">+5%</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ number_format($totalBranches) }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Total Branches') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-tertiary-fixed text-tertiary rounded-lg material-symbols-outlined">workspace_premium</span>
                    <span class="text-blue-600 font-label-sm text-label-sm">82%</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">{{ $dominantTier }}</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Dominant Tier') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <span class="p-2 bg-error-container text-error rounded-lg material-symbols-outlined">credit_card_off</span>
                    <span class="text-error font-label-sm text-label-sm">-2%</span>
                </div>
                <div class="text-2xl font-bold text-on-surface">0</div>
                <div class="text-on-surface-variant text-body-sm font-body-sm">{{ __('Failed Payments') }}</div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <!-- Filters -->
            <form method="GET" action="{{ route('clinics.index') }}" class="p-6 border-b border-slate-100 flex flex-wrap gap-4 items-center justify-between bg-slate-50/50">
                <div class="flex gap-4 items-center">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <span class="material-symbols-outlined text-[20px]">filter_list</span>
                        </span>
                        <select name="tier" onchange="this.form.submit()" class="pl-10 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-sm appearance-none focus:ring-2 focus:ring-primary focus:border-transparent outline-none cursor-pointer">
                            <option value="">{{ __('All Tiers') }}</option>
                            <option value="enterprise" @selected(($filters['tier'] ?? '') === 'enterprise')>{{ __('Enterprise') }}</option>
                            <option value="professional" @selected(($filters['tier'] ?? '') === 'professional')>{{ __('Professional') }}</option>
                            <option value="standard" @selected(($filters['tier'] ?? '') === 'standard')>{{ __('Standard') }}</option>
                        </select>
                    </div>
                    <div class="relative">
                        <select name="status" onchange="this.form.submit()" class="pl-4 pr-8 py-2 bg-white border border-slate-200 rounded-lg text-sm appearance-none focus:ring-2 focus:ring-primary focus:border-transparent outline-none cursor-pointer">
                            <option value="">{{ __('Any Status') }}</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('Active') }}</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                </div>
                @if ($organizations->total() > 0)
                    <div class="text-body-sm font-body-sm text-on-surface-variant">
                        {{ __('Showing') }}
                        <span class="font-bold">{{ $organizations->firstItem() }}-{{ $organizations->lastItem() }}</span>
                        {{ __('of') }}
                        <span class="font-bold">{{ number_format($organizations->total()) }}</span>
                        {{ __('clinics') }}
                    </div>
                @endif
            </form>

            <!-- Data Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Clinic Name') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Primary Admin') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Branch Count') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Subscription Tier') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($organizations as $organization)
                            @php
                                $tier = $tierForOrganization($organization);
                                $admin = $primaryAdminFor($organization);
                                $avatarClass = $avatarClasses[$loop->index % count($avatarClasses)];
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg {{ $avatarClass }} flex items-center justify-center font-bold">
                                            {{ $initialsFor($organization->name) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $organization->name }}</div>
                                            <div class="text-xs text-slate-500">ID: DF-{{ str_pad((string) $organization->id, 5, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($admin)
                                        <div class="text-sm text-slate-900">{{ $admin->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $admin->email }}</div>
                                    @else
                                        <div class="text-sm text-slate-500">{{ $organization->email ?? '—' }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        {{ $organization->branches_count }}
                                        {{ $organization->branches_count === 1 ? __('Branch') : __('Branches') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="material-symbols-outlined {{ $tier['iconClass'] }} text-[18px]"
                                            @if ($tier['filled']) style="font-variation-settings: 'FILL' 1;" @endif
                                        >{{ $tier['icon'] }}</span>
                                        <span class="text-sm font-semibold text-slate-900">{{ $tier['label'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($organization->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            {{ __('ACTIVE') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            {{ __('INACTIVE') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('update', $organization)
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('clinics.edit', $organization) }}" class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all" title="{{ __('Edit Clinic') }}">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </a>
                                            <a href="{{ route('clinics.branches.index', $organization) }}" class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all" title="{{ __('Manage Branches') }}">
                                                <span class="material-symbols-outlined text-[20px]">account_tree</span>
                                            </a>
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-body-md text-on-surface-variant">
                                    {{ __('No clinics found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($organizations->hasPages())
                <div class="p-6 border-t border-slate-100 flex items-center justify-between">
                    @if ($organizations->onFirstPage())
                        <button type="button" class="flex items-center gap-1 px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 transition-colors disabled:opacity-50" disabled>
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                            {{ __('Previous') }}
                        </button>
                    @else
                        <a href="{{ $organizations->previousPageUrl() }}" class="flex items-center gap-1 px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                            {{ __('Previous') }}
                        </a>
                    @endif

                    <div class="flex gap-1">
                        @for ($page = 1; $page <= min($organizations->lastPage(), 3); $page++)
                            <a
                                href="{{ $organizations->url($page) }}"
                                class="w-10 h-10 flex items-center justify-center rounded-lg text-sm {{ $page === $organizations->currentPage() ? 'bg-blue-600 text-white font-bold' : 'hover:bg-slate-100 text-slate-600' }}"
                            >
                                {{ $page }}
                            </a>
                        @endfor
                        @if ($organizations->lastPage() > 3)
                            <span class="w-10 h-10 flex items-center justify-center text-slate-400">...</span>
                            <a
                                href="{{ $organizations->url($organizations->lastPage()) }}"
                                class="w-10 h-10 flex items-center justify-center rounded-lg text-sm {{ $organizations->currentPage() === $organizations->lastPage() ? 'bg-blue-600 text-white font-bold' : 'hover:bg-slate-100 text-slate-600' }}"
                            >
                                {{ $organizations->lastPage() }}
                            </a>
                        @endif
                    </div>

                    @if ($organizations->hasMorePages())
                        <a href="{{ $organizations->nextPageUrl() }}" class="flex items-center gap-1 px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                            {{ __('Next') }}
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </a>
                    @else
                        <button type="button" class="flex items-center gap-1 px-4 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 transition-colors disabled:opacity-50" disabled>
                            {{ __('Next') }}
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
