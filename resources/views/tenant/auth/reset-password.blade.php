<x-guest-layout>
    <div class="bg-white rounded-xl shadow-sm border border-outline-variant p-8 md:p-10">
        <div class="text-center mb-10">
            <h1 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Reset Password') }}</h1>
            <p class="font-body-md text-on-surface-variant">{{ $branchName }}</p>
            <p class="text-xs text-slate-500 mt-2">
                {{ __('Choose a new password for your staff account.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('tenant.password.store') }}" class="space-y-6">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label class="block font-label-md text-on-surface-variant mb-2" for="email">{{ __('Email Address') }}</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">mail</span>
                    <input
                        class="w-full pl-10 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md @error('email') border-error @enderror"
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $request->email) }}"
                        placeholder="staff@clinic.test"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label class="block font-label-md text-on-surface-variant mb-2" for="password">{{ __('Password') }}</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">lock</span>
                    <input
                        class="w-full pl-10 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md @error('password') border-error @enderror"
                        id="password"
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        required
                        autocomplete="new-password"
                    />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <label class="block font-label-md text-on-surface-variant mb-2" for="password_confirmation">{{ __('Confirm Password') }}</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">lock</span>
                    <input
                        class="w-full pl-10 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md @error('password_confirmation') border-error @enderror"
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="••••••••"
                        required
                        autocomplete="new-password"
                    />
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button class="w-full py-3 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity" type="submit">
                {{ __('Reset Password') }}
            </button>
        </form>
    </div>

    <p class="text-center mt-8 font-body-sm text-on-surface-variant">
        <a class="text-primary font-label-md hover:underline" href="{{ route('tenant.login') }}">{{ __('Back to Sign In') }}</a>
    </p>
</x-guest-layout>
