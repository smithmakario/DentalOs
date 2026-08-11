@extends('layouts.get-started')

@section('title', __('Request Access'))

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-8 text-center">
            <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-on-surface mb-3">{{ __('Request Access to DentalOs') }}</h1>
            <p class="text-on-surface-variant leading-relaxed">
                {{ __('Tell us about your dental organization. Our team will review your application and get back to you shortly.') }}
            </p>
        </div>

        <div class="bg-surface-elevated rounded-2xl border border-outline/30 shadow-soft-lg p-6 sm:p-8">
            <form method="POST" action="{{ route('get-started.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Organization Name') }} *</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                        placeholder="{{ __('e.g. BrightSmile Dental Group') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-outline/50 bg-white text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('name') border-red-400 @enderror"/>
                    <x-input-error :messages="$errors->get('name')" class="mt-1"/>
                </div>

                <div>
                    <label for="location" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Location') }} *</label>
                    <input id="location" name="location" type="text" value="{{ old('location') }}" required
                        placeholder="{{ __('City, State / Region') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-outline/50 bg-white text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('location') border-red-400 @enderror"/>
                    <x-input-error :messages="$errors->get('location')" class="mt-1"/>
                </div>

                <div>
                    <label for="government_approval" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Government Approval / License Number') }} *</label>
                    <input id="government_approval" name="government_approval" type="text" value="{{ old('government_approval') }}" required
                        placeholder="{{ __('e.g. MOH-2024-12345') }}"
                        class="w-full px-4 py-2.5 rounded-xl border border-outline/50 bg-white text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('government_approval') border-red-400 @enderror"/>
                    <p class="mt-1 text-xs text-on-surface-variant/80">{{ __('Your dental practice license or regulatory approval reference number.') }}</p>
                    <x-input-error :messages="$errors->get('government_approval')" class="mt-1"/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_person" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Contact Person') }} *</label>
                        <input id="contact_person" name="contact_person" type="text" value="{{ old('contact_person') }}" required
                            placeholder="{{ __('Dr. Jane Smith') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-outline/50 bg-white text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('contact_person') border-red-400 @enderror"/>
                        <x-input-error :messages="$errors->get('contact_person')" class="mt-1"/>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Phone Number') }} *</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                            placeholder="+234 800 000 0000"
                            class="w-full px-4 py-2.5 rounded-xl border border-outline/50 bg-white text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('phone') border-red-400 @enderror"/>
                        <x-input-error :messages="$errors->get('phone')" class="mt-1"/>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Email Address') }} *</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        placeholder="admin@clinic.com"
                        class="w-full px-4 py-2.5 rounded-xl border border-outline/50 bg-white text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all @error('email') border-red-400 @enderror"/>
                    <x-input-error :messages="$errors->get('email')" class="mt-1"/>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-cta text-on-surface rounded-xl font-semibold hover:bg-cta-dark transition-colors shadow-soft cursor-pointer">
                        {{ __('Submit Request') }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </button>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-on-surface-variant">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-primary-dark">{{ __('Sign in') }}</a>
        </p>
    </div>
@endsection
