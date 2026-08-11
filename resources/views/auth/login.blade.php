<x-guest-layout>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant p-8 md:p-10">
        <div class="text-center mb-10">
            <h1 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Welcome Back') }}</h1>
            <p class="font-body-md text-on-surface-variant">{{ __('Clinical Practice Management Access') }}</p>
            {{-- <p class="text-sm text-gray-600 mt-1">Platform sign in</p> --}}
            <p class="text-xs text-gray-500 mt-1">Super admin & organization dashboard</p>
        </div>

        <x-auth-session-status class="mb-4 font-body-sm text-secondary" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block font-label-md text-on-surface-variant mb-2" for="email">{{ __('Email Address') }}</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="mail">mail</span>
                    <input
                        class="w-full pl-10 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md @error('email') border-error @enderror"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="practitioner@dentaflow.com"
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
                    @if (Route::has('password.request'))
                        <a class="font-label-md text-primary hover:underline" href="{{ route('password.request') }}">
                            {{ __('Forgot Password?') }}
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="lock">lock</span>
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
                    id="remember"
                    name="remember"
                    type="checkbox"
                />
                <label class="ml-2 font-body-sm text-on-surface-variant" for="remember">
                    {{ __('Keep me signed in for 30 days') }}
                </label>
            </div>

            <button class="w-full py-3.5 px-6 bg-primary text-on-primary font-label-md rounded-lg shadow-md hover:bg-primary-container transition-all active:opacity-80 duration-150 flex justify-center items-center gap-2" type="submit">
                <span>{{ __('Sign In') }}</span>
                <span class="material-symbols-outlined text-[20px]" data-icon="arrow_forward">arrow_forward</span>
            </button>
        </form>

        <div class="relative my-8">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-outline-variant"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-white font-body-sm text-outline">{{ __('Or continue with') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button type="button" class="flex items-center justify-center gap-2 py-3 px-4 bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors font-label-md text-on-surface">
                <img
                    alt="Google"
                    class="w-5 h-5"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuCDCvB8putYaRYajyivSt9-ge-eW-idGFZ4QqtyphSeVHP5WxyeKHBwfKEbmcC09huJ5ocBZMojrQQZtKD6P2fhHxKA7MVeoD-KFmsDJWb4MKXKfDpoNWjN6tc1BFidTHI_Qe3LZY6wHMG_jobYs6tV2wlXPKowGkQmVub9RZMoT4svS64gN1M_Z22f622Mlk7INLSLrRwOGbG6Gbfl-2TUmktydMrGijg9RdbZyXFDwgCpIOw2G5D5Hza4cfKhP4AkgP5JCEnEPYk"
                />
                <span>{{ __('Google') }}</span>
            </button>
            <button type="button" class="flex items-center justify-center gap-2 py-3 px-4 bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors font-label-md text-on-surface">
                <span class="material-symbols-outlined text-outline" data-icon="hub">hub</span>
                <span>{{ __('SSO') }}</span>
            </button>
        </div>
    </div>

    <p class="text-center mt-8 font-body-sm text-on-surface-variant">
        {{ __("Don't have an account?") }}
        <a class="text-primary font-label-md hover:underline" href="#">{{ __('Contact Administration') }}</a>
    </p>
</x-guest-layout>
