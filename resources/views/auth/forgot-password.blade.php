<x-guest-layout>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant p-8 md:p-10">
        <div class="text-center mb-10">
            <h1 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Forgot Password') }}</h1>
            <p class="font-body-md text-on-surface-variant">
                {{ __('Enter your email and we will send you a password reset link.') }}
            </p>
        </div>

        <x-auth-session-status class="mb-4 font-body-sm text-secondary" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
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

            <button class="w-full py-3.5 px-6 bg-primary text-on-primary font-label-md rounded-lg shadow-md hover:bg-primary-container transition-all active:opacity-80 duration-150 flex justify-center items-center gap-2" type="submit">
                <span>{{ __('Email Password Reset Link') }}</span>
                <span class="material-symbols-outlined text-[20px]" data-icon="arrow_forward">arrow_forward</span>
            </button>
        </form>
    </div>

    <p class="text-center mt-8 font-body-sm text-on-surface-variant">
        <a class="text-primary font-label-md hover:underline" href="{{ route('login') }}">{{ __('Back to Sign In') }}</a>
    </p>
</x-guest-layout>
