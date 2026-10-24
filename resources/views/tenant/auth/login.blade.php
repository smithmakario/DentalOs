<x-guest-layout>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant p-8 md:p-10">
        <div class="text-center mb-10">
            @if (! empty($branchLogoUrl))
                <img
                    src="{{ $branchLogoUrl }}"
                    alt="{{ $clinicName }}"
                    class="mx-auto mb-4 h-16 w-16 rounded-xl object-contain border border-outline-variant bg-surface-container-low"
                />
            @endif
            <p class="text-xs font-semibold uppercase tracking-wider text-primary mb-2">{{ $clinicName ?? config('app.name') }}</p>
            <h1 class="font-h1 text-h2 text-on-surface mb-2">
                {{ __('Welcome to :clinic', ['clinic' => $clinicName ?? __('your clinic')]) }}
            </h1>
            <p class="font-body-md text-on-surface-variant">
                {{ __('Sign in to :branch', ['branch' => $branchName ?? __('this branch')]) }}
            </p>
            <p class="text-xs text-slate-500 mt-2">{{ __('Primary admins and branch staff use this same login link.') }}</p>
        </div>

        @if ($signedInStaff)
            <div class="mb-6 rounded-lg border border-primary/20 bg-primary-fixed/40 px-4 py-3 text-sm text-on-surface">
                <p class="font-medium">{{ __('Welcome back, :name.', ['name' => $signedInStaff->first_name ?: $signedInStaff->full_name]) }}</p>
                <p class="text-outline mt-1">{{ __('You are already signed in on this device.') }}</p>
                @if ($dashboardUrl)
                    <a href="{{ $dashboardUrl }}" class="inline-flex mt-3 font-label-md text-primary hover:underline">
                        {{ __('Continue to dashboard') }}
                    </a>
                @endif
            </div>
        @endif

        <x-auth-session-status class="mb-4 font-body-sm text-secondary" :status="session('status')" />

        <form method="POST" action="{{ tenant() ? \App\Support\TenantPortal::absolute(tenant(), '/staff/login') : url()->current() }}" class="space-y-6">
            @csrf

            <div>
                <label class="block font-label-md text-on-surface-variant mb-2" for="email">{{ __('Email Address') }}</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">mail</span>
                    <input
                        class="w-full pl-10 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md @error('email') border-error @enderror"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="staff@clinic.test"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block font-label-md text-on-surface-variant" for="password">{{ __('Password') }}</label>
                    @if (tenant())
                        <a class="font-label-md text-primary hover:underline" href="{{ \App\Support\TenantPortal::absolute(tenant(), '/staff/forgot-password') }}">
                            {{ __('Forgot Password?') }}
                        </a>
                    @endif
                </div>
                <x-password-input class="@error('password') border-error @enderror" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center">
                <input
                    class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary"
                    id="remember_me"
                    name="remember"
                    type="checkbox"
                />
                <label class="ms-2 font-body-sm text-on-surface-variant" for="remember_me">{{ __('Remember me') }}</label>
            </div>

            <button class="w-full py-3 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity" type="submit">
                {{ __('Sign In') }}
            </button>
        </form>

        @if (tenant())
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-outline-variant"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white font-body-sm text-outline">{{ __('Or continue with') }}</span>
                </div>
            </div>

            <a
                href="{{ \App\Support\TenantPortal::absolute(tenant(), '/staff/auth/google') }}"
                class="flex w-full items-center justify-center gap-2 py-3 px-4 bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors font-label-md text-on-surface"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-1.6 3.9-5.5 3.9-3.3 0-6-2.7-6-6s2.7-6 6-6c1.9 0 3.1.8 3.8 1.5l2.6-2.5C16.8 3.4 14.6 2.4 12 2.4 6.9 2.4 2.7 6.6 2.7 11.7S6.9 21 12 21c5.4 0 9-3.8 9-9.1 0-.6-.1-1.1-.2-1.7H12z"/>
                </svg>
                <span>{{ __('Continue with Google') }}</span>
            </a>
        @endif
    </div>
</x-guest-layout>
