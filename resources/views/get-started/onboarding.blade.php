@extends('layouts.get-started')

@section('title', __('Complete Onboarding'))

@section('content')
    @php
        $inputClass = 'w-full px-4 py-2.5 rounded-xl border border-outline/50 bg-white text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all';
    @endphp

    <div
        class="max-w-2xl mx-auto"
        x-data="{
            appHost: @js($appHost),
            mainLabel: @js(__('Main')),
            clinicName: @js(old('name', $registrationRequest->name)),
            branchName: @js(old('branch_name', $suggestedBranchName)),
            branchSlug: @js(old('branch_slug', '')),
            branchNameTouched: {{ old('branch_name') ? 'true' : 'false' }},
            slugify(value) {
                return String(value || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .slice(0, 63);
            },
            suggestBranchName(name) {
                const trimmed = String(name || '').trim();

                return trimmed ? `${trimmed} - ${this.mainLabel}` : '';
            },
            syncFromClinicName() {
                if (! this.branchNameTouched) {
                    this.branchName = this.suggestBranchName(this.clinicName);
                }

                this.syncFromBranchName();
            },
            syncFromBranchName() {
                this.branchSlug = this.slugify(this.branchName);
            },
            pathAccessPreview() {
                if (! this.branchSlug) {
                    return '';
                }

                const clinicSlug = this.slugify(this.clinicName);

                if (! clinicSlug) {
                    return '';
                }

                return `${this.appHost}/${clinicSlug}/${this.branchSlug}`;
            },
            init() {
                this.syncFromClinicName();
            },
        }"
    >
        <div class="mb-8 text-center">
            <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-on-surface mb-3">{{ __('Set up your clinic') }}</h1>
            <p class="text-on-surface-variant leading-relaxed">
                {{ __('Confirm your clinic details, first branch, and primary admin account to finish onboarding.') }}
            </p>
        </div>

        <div class="bg-surface-elevated rounded-2xl border border-outline/30 shadow-soft-lg p-6 sm:p-8">
            <form
                method="POST"
                action="{{ route('onboarding.store', $registrationRequest->onboarding_token) }}"
                enctype="multipart/form-data"
                class="space-y-8"
                @submit="syncFromClinicName()"
            >
                @csrf

                <section class="space-y-4">
                    <h2 class="font-display text-lg font-bold text-on-surface">{{ __('Clinic profile') }}</h2>

                    <div>
                        <label for="name" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Clinic Name') }} *</label>
                        <input id="name" name="name" type="text" x-model="clinicName" @input="syncFromClinicName()" required
                            class="{{ $inputClass }} @error('name') border-red-400 @enderror"/>
                        <x-input-error :messages="$errors->get('name')" class="mt-1"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Email') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $registrationRequest->email) }}"
                                class="{{ $inputClass }} @error('email') border-red-400 @enderror"/>
                            <x-input-error :messages="$errors->get('email')" class="mt-1"/>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Phone') }}</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $registrationRequest->phone) }}"
                                class="{{ $inputClass }} @error('phone') border-red-400 @enderror"/>
                            <x-input-error :messages="$errors->get('phone')" class="mt-1"/>
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Address') }}</label>
                        <input id="address" name="address" type="text" value="{{ old('address', $registrationRequest->location) }}"
                            class="{{ $inputClass }} @error('address') border-red-400 @enderror"/>
                        <x-input-error :messages="$errors->get('address')" class="mt-1"/>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Organization type') }} *</label>
                        <select id="type" name="type" required class="{{ $inputClass }} @error('type') border-red-400 @enderror">
                            <option value="single" @selected(old('type', 'single') === 'single')>{{ __('Single practice') }}</option>
                            <option value="dso" @selected(old('type') === 'dso')>{{ __('Multi-location / DSO') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-1"/>
                    </div>

                    <div>
                        <label for="logo" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Clinic logo') }}</label>
                        <input id="logo" name="logo" type="file" accept="image/png,image/jpeg"
                            class="block w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:font-semibold"/>
                        <x-input-error :messages="$errors->get('logo')" class="mt-1"/>
                    </div>
                </section>

                <section class="space-y-4 border-t border-outline/30 pt-8">
                    <div>
                        <h2 class="font-display text-lg font-bold text-on-surface">{{ __('First branch') }}</h2>
                        <p class="text-sm text-on-surface-variant mt-1">
                            {{ __('Branch name is suggested for you. The slug is generated automatically.') }}
                        </p>
                    </div>

                    <div>
                        <label for="branch_name" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Branch Name') }} *</label>
                        <input id="branch_name" name="branch_name" type="text" x-model="branchName"
                            @input="branchNameTouched = true; syncFromBranchName()" required
                            class="{{ $inputClass }} @error('branch_name') border-red-400 @enderror"/>
                        <x-input-error :messages="$errors->get('branch_name')" class="mt-1"/>
                    </div>

                    <div>
                        <label for="branch_slug" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Branch Slug') }}</label>
                        <input id="branch_slug" name="branch_slug" type="text" x-model="branchSlug" readonly tabindex="-1"
                            class="{{ $inputClass }} bg-slate-50 text-on-surface-variant @error('branch_slug') border-red-400 @enderror"/>
                        <p class="mt-1 text-xs text-on-surface-variant/80" x-show="pathAccessPreview()" x-cloak>
                            {{ __('This is where it will be accessed:') }}
                            <span class="font-semibold" x-text="pathAccessPreview()"></span>
                        </p>
                        <x-input-error :messages="$errors->get('branch_slug')" class="mt-1"/>
                    </div>
                </section>

                <section class="space-y-4 border-t border-outline/30 pt-8">
                    <h2 class="font-display text-lg font-bold text-on-surface">{{ __('Primary admin') }}</h2>

                    <div>
                        <label for="admin_name" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Admin name') }} *</label>
                        <input id="admin_name" name="admin_name" type="text" value="{{ old('admin_name', $registrationRequest->contact_person) }}" required
                            class="{{ $inputClass }} @error('admin_name') border-red-400 @enderror"/>
                        <x-input-error :messages="$errors->get('admin_name')" class="mt-1"/>
                    </div>

                    <div>
                        <label for="admin_email" class="block text-sm font-semibold text-on-surface-variant mb-1.5">{{ __('Admin email') }} *</label>
                        <input id="admin_email" name="admin_email" type="email" value="{{ old('admin_email', $registrationRequest->email) }}" required
                            class="{{ $inputClass }} @error('admin_email') border-red-400 @enderror"/>
                        <x-input-error :messages="$errors->get('admin_email')" class="mt-1"/>
                    </div>

                    <div>
                        <x-password-with-generate
                            id="admin_password"
                            name="admin_password"
                            :label="__('Admin password')"
                            :required="true"
                            :input-class="$inputClass.($errors->has('admin_password') ? ' border-red-400' : '')"
                        />
                    </div>
                </section>

                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-cta text-on-surface rounded-xl font-semibold hover:bg-cta-dark transition-colors shadow-soft cursor-pointer">
                    {{ __('Finish onboarding') }}
                </button>
            </form>
        </div>
    </div>
@endsection
