<x-guest-layout>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant p-8 md:p-10">
        <div class="text-center mb-10">
            <h1 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Branch Sign In') }}</h1>
            <p class="font-body-md text-on-surface-variant">{{ $branchName }}</p>
            <p class="text-xs text-slate-500 mt-2">{{ __('Staff access for clinical operations') }}</p>
        </div>

        <x-auth-session-status class="mb-4 font-body-sm text-secondary" :status="session('status')" />

        <form method="POST" action="{{ route('tenant.login') }}" class="space-y-6">
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
                    @if (Route::has('tenant.password.request'))
                        <a class="font-label-md text-primary hover:underline" href="{{ route('tenant.password.request') }}">
                            {{ __('Forgot Password?') }}
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">lock</span>
                    <input
                        class="w-full pl-10 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md @error('password') border-error @enderror"
                        id="password"
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                </div>
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
                {{ __('Sign In to Branch') }}
            </button>
        </form>
    </div>
</x-guest-layout>
