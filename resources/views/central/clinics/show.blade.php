@php
    use App\Enums\BillingCycle;
    use App\Enums\OrganizationRole;
    use App\Support\TenantPortal;

    $primaryAdmin = $organization->users->first(
        fn ($user) => in_array($user->pivot->role, [OrganizationRole::Owner, OrganizationRole::Admin], true)
    );
    $adminBranch = $organization->adminBranch;
    $clinicLoginUrl = $adminBranch ? TenantPortal::loginUrl($adminBranch) : null;
@endphp

<x-app-layout>
    <div class="p-8 max-w-5xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('clinics.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to Clinics') }}
            </a>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-start gap-4 min-w-0">
                    @if (! empty($clinicLogoUrl))
                        <img
                            src="{{ $clinicLogoUrl }}"
                            alt="{{ $organization->name }}"
                            class="h-14 w-14 rounded-xl object-contain border border-slate-200 bg-white shrink-0"
                        />
                    @endif
                    <div class="min-w-0">
                        <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ $organization->name }}</h2>
                        <p class="font-body-md text-on-surface-variant">
                            {{ __('Clinic overview, subscription, staff, and branches.') }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @can('update', $organization)
                        <a href="{{ route('clinics.edit', $organization) }}" class="inline-flex items-center gap-2 px-4 py-2.5 border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-surface-container-low transition-colors">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                            {{ __('Edit') }}
                        </a>
                    @endcan
                    <a href="{{ route('clinics.staff.index', $organization) }}" class="inline-flex items-center gap-2 px-4 py-2.5 border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined text-[18px]">group</span>
                        {{ __('Manage Staff') }}
                    </a>
                    <a href="{{ route('clinics.branches.index', $organization) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-[18px]">account_tree</span>
                        {{ __('Manage Branches') }}
                    </a>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h3 class="font-label-md text-on-surface mb-4">{{ __('Clinic details') }}</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-on-surface-variant mb-1">{{ __('Status') }}</dt>
                            <dd>
                                @if ($organization->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">{{ __('ACTIVE') }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200">{{ __('INACTIVE') }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant mb-1">{{ __('Type') }}</dt>
                            <dd class="text-on-surface font-medium">{{ $organization->type->subscriptionTierLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant mb-1">{{ __('Slug') }}</dt>
                            <dd class="text-on-surface font-mono text-xs">{{ $organization->slug }}</dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant mb-1">{{ __('Branches') }}</dt>
                            <dd class="text-on-surface font-medium">{{ $organization->branches_count }}</dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant mb-1">{{ __('Email') }}</dt>
                            <dd class="text-on-surface">{{ $organization->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-on-surface-variant mb-1">{{ __('Phone') }}</dt>
                            <dd class="text-on-surface">{{ $organization->phone ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-on-surface-variant mb-1">{{ __('Address') }}</dt>
                            <dd class="text-on-surface">{{ $organization->address ?: '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-on-surface-variant mb-1">{{ __('Primary admin') }}</dt>
                            <dd class="text-on-surface">
                                @if ($primaryAdmin)
                                    {{ $primaryAdmin->name }} · {{ $primaryAdmin->email }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        @if ($clinicLoginUrl)
                            <div class="sm:col-span-2">
                                <dt class="text-on-surface-variant mb-1">{{ __('Clinic login link') }}</dt>
                                <dd class="text-on-surface">
                                    <p class="text-xs text-on-surface-variant mb-2">{{ __('Share this URL with the primary admin and branch staff. Everyone signs in here.') }}</p>
                                    <a href="{{ $clinicLoginUrl }}" class="font-mono text-sm text-primary break-all hover:underline" target="_blank" rel="noopener">
                                        {{ $clinicLoginUrl }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>

                    @if (auth()->user()?->isSuperAdmin())
                        <form method="POST" action="{{ route('clinics.primary-admin.update', $organization) }}" class="mt-6 pt-6 border-t border-slate-100 space-y-4">
                            @csrf
                            @method('PATCH')
                            <h4 class="font-label-md text-on-surface">{{ __('Edit primary admin') }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="admin_name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Admin name') }}</label>
                                    <input id="admin_name" name="admin_name" type="text" required
                                        value="{{ old('admin_name', $primaryAdmin?->name) }}"
                                        class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none" />
                                    <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="admin_email" class="block font-label-md text-on-surface-variant mb-2">{{ __('Admin email') }}</label>
                                    <input id="admin_email" name="admin_email" type="email" required
                                        value="{{ old('admin_email', $primaryAdmin?->email) }}"
                                        class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none" />
                                    <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-password-with-generate
                                        id="admin_password"
                                        name="admin_password"
                                        :label="__('New password')"
                                        :with-confirmation="true"
                                        :hint="__('Leave blank to keep the current password.')"
                                        input-class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none"
                                    />
                                </div>
                            </div>
                            <button type="submit" class="px-4 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                                {{ __('Save primary admin') }}
                            </button>
                        </form>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-label-md text-on-surface">{{ __('Branches') }}</h3>
                        <a href="{{ route('clinics.branches.index', $organization) }}" class="text-primary font-label-md hover:underline text-sm">{{ __('Manage') }}</a>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @forelse ($organization->branches as $branch)
                            <li class="py-3 flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-medium text-on-surface flex items-center gap-2">
                                        {{ $branch->name }}
                                        @if ($organization->isAdminBranch($branch))
                                            <span class="text-[10px] uppercase tracking-wide font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100">{{ __('Admin') }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-on-surface-variant font-mono">{{ $organization->slug }}/{{ $branch->slug }}</div>
                                </div>
                                @if ($branch->is_active)
                                    <span class="text-xs font-bold text-green-700">{{ __('Active') }}</span>
                                @else
                                    <span class="text-xs font-bold text-red-700">{{ __('Inactive') }}</span>
                                @endif
                            </li>
                        @empty
                            <li class="py-6 text-on-surface-variant text-sm">{{ __('No branches yet.') }}</li>
                        @endforelse
                    </ul>

                    @if (auth()->user()?->isSuperAdmin() && $organization->branches->isNotEmpty())
                        <form method="POST" action="{{ route('clinics.admin-branch.assign', $organization) }}" class="mt-6 pt-6 border-t border-slate-100 space-y-3">
                            @csrf
                            <div>
                                <label for="admin_branch_id" class="block font-label-md text-on-surface-variant mb-2">{{ __('Admin branch') }}</label>
                                <select id="admin_branch_id" name="admin_branch_id" required
                                    class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none">
                                    @foreach ($organization->branches as $branch)
                                        <option value="{{ $branch->id }}" @selected((string) old('admin_branch_id', $organization->admin_branch_id) === (string) $branch->id)>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-on-surface-variant">
                                    {{ __('Clinic admins who sign in to this branch can manage all branches for the clinic.') }}
                                </p>
                                <x-input-error :messages="$errors->get('admin_branch_id')" class="mt-2" />
                            </div>
                            <button type="submit" class="px-4 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                                {{ __('Save admin branch') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h3 class="font-label-md text-on-surface mb-4">{{ __('Subscription') }}</h3>

                    @if ($subscription)
                        <dl class="space-y-3 text-sm mb-6">
                            <div>
                                <dt class="text-on-surface-variant mb-1">{{ __('Plan') }}</dt>
                                <dd class="font-medium text-on-surface">{{ $subscription->plan?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-on-surface-variant mb-1">{{ __('Billing cycle') }}</dt>
                                <dd class="text-on-surface">{{ ucfirst($subscription->billing_cycle->value) }}</dd>
                            </div>
                            <div>
                                <dt class="text-on-surface-variant mb-1">{{ __('Status') }}</dt>
                                <dd class="text-on-surface">{{ str_replace('_', ' ', ucfirst($subscription->status->value)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-on-surface-variant mb-1">{{ __('Period ends') }}</dt>
                                <dd class="text-on-surface">{{ $subscription->current_period_end?->timezone(config('app.timezone'))->format('M j, Y') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-on-surface-variant mb-1">{{ __('Staff access') }}</dt>
                                <dd class="text-on-surface">
                                    @if ($allowsStaffAccess)
                                        <span class="text-green-700 font-medium">{{ __('Allowed') }}</span>
                                    @else
                                        <span class="text-red-700 font-medium">{{ __('Blocked') }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                        <p class="text-xs text-on-surface-variant mb-4">
                            {{ __('Staff login is blocked :days days after the period end if payment is still outstanding.', ['days' => $graceDays]) }}
                        </p>
                        @can('update', $organization)
                            <a href="{{ route('subscriptions.checkout', $organization) }}" class="inline-flex w-full justify-center items-center gap-2 px-4 py-2.5 border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-surface-container-low transition-colors">
                                {{ __('Collect payment') }}
                            </a>
                        @endcan
                    @else
                        <p class="text-sm text-on-surface-variant mb-4">
                            {{ __('This clinic is inactive until a subscription plan is assigned.') }}
                        </p>
                    @endif

                    @can('update', $organization)
                        @if ($plans->isEmpty())
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-900">
                                {{ __('Create an active subscription plan for this organization type before you can activate the clinic.') }}
                                <a href="{{ route('subscription-plans.create') }}" class="block mt-2 font-medium text-primary hover:underline">{{ __('Create subscription plan') }}</a>
                            </div>
                        @else
                            <form method="POST" action="{{ route('clinics.subscription.assign', $organization) }}" class="space-y-4 {{ $subscription ? 'pt-4 border-t border-slate-100' : '' }}">
                                @csrf
                                <div>
                                    <label for="subscription_plan_id" class="block font-label-md text-on-surface-variant mb-2">{{ $subscription ? __('Change plan') : __('Assign plan') }} *</label>
                                    <select id="subscription_plan_id" name="subscription_plan_id" required
                                        class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none @error('subscription_plan_id') border-error @enderror">
                                        <option value="">{{ __('Select a plan') }}</option>
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->id }}" @selected((string) old('subscription_plan_id', $subscription?->subscription_plan_id) === (string) $plan->id)>
                                                {{ $plan->name }} — ₦{{ number_format((float) $plan->price_monthly, 0) }}/{{ __('mo') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('subscription_plan_id')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="billing_cycle" class="block font-label-md text-on-surface-variant mb-2">{{ __('Billing cycle') }} *</label>
                                    <select id="billing_cycle" name="billing_cycle" required
                                        class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none @error('billing_cycle') border-error @enderror">
                                        @foreach ($billingCycles as $cycle)
                                            <option value="{{ $cycle->value }}" @selected(old('billing_cycle', $subscription?->billing_cycle->value ?? BillingCycle::Monthly->value) === $cycle->value)>
                                                {{ $cycle === BillingCycle::Yearly ? __('Yearly') : __('Monthly') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('billing_cycle')" class="mt-2" />
                                </div>
                                <p class="text-xs text-on-surface-variant">
                                    {{ __('Assigning a plan activates the clinic and branches. Payment can wait until the period is due; staff access is suspended after a :days-day grace period.', ['days' => $graceDays]) }}
                                </p>
                                <button type="submit" class="w-full px-4 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                                    {{ $subscription ? __('Update subscription') : __('Assign & activate') }}
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
