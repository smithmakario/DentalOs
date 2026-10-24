<x-guest-layout>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant p-8 md:p-10">
        <div class="text-center mb-10">
            <h1 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Platform Sign In') }}</h1>
            <p class="font-body-md text-on-surface-variant">{{ __('DentalOs operator access only') }}</p>
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
                <x-password-input class="@error('password') border-error @enderror" />
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

        <a
            href="{{ route('auth.google') }}"
            class="flex w-full items-center justify-center gap-2 py-3 px-4 bg-white border border-outline-variant rounded-lg hover:bg-surface-container-low transition-colors font-label-md text-on-surface"
        >
            <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.3-1.6 3.9-5.5 3.9-3.3 0-6-2.7-6-6s2.7-6 6-6c1.9 0 3.1.8 3.8 1.5l2.6-2.5C16.8 3.4 14.6 2.4 12 2.4 6.9 2.4 2.7 6.6 2.7 11.7S6.9 21 12 21c5.4 0 9-3.8 9-9.1 0-.6-.1-1.1-.2-1.7H12z"/>
            </svg>
            <span>{{ __('Continue with Google') }}</span>
        </a>
    </div>

    <p class="text-center mt-8 font-body-sm text-on-surface-variant">
        {{ __("Don't have an account?") }}
        <a class="text-primary font-label-md hover:underline" href="#">{{ __('Contact Administration') }}</a>
    </p>
</x-guest-layout>
