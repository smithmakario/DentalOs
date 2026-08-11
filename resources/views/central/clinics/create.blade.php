@php
    $initialStep = match (true) {
        $errors->hasAny(['admin_name', 'admin_email', 'admin_password']) => 4,
        $errors->has('type') => 3,
        $errors->hasAny(['branch_name', 'branch_slug', 'domain']) => 2,
        default => 1,
    };

    $inputClass = 'w-full px-md py-sm rounded-lg border border-outline text-body-md bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all';
    $inputErrorClass = 'border-error focus:border-error focus:ring-error/20';
    $stepPanelStyle = fn (int $step): string => $initialStep === $step ? '' : 'display: none;';
    $wizardSteps = [
        ['number' => 1, 'label' => __('Clinic Profile')],
        ['number' => 2, 'label' => __('Branches')],
        ['number' => 3, 'label' => __('Subscription')],
        ['number' => 4, 'label' => __('Primary Admin')],
    ];
    $nextStepLabels = [
        1 => __('Next: Branches'),
        2 => __('Next: Subscription'),
        3 => __('Next: Primary Admin'),
    ];
@endphp

<x-app-layout>
    <div
        class="max-w-5xl mx-auto px-xl py-xl"
        x-data="{
            step: {{ $initialStep }},
            organizationType: @js(old('type', 'single')),
            logoPreview: null,
            totalSteps: 4,
            steps: @js($wizardSteps),
            nextStepLabels: @js($nextStepLabels),
            progressWidth() {
                return (this.step / this.totalSteps) * 100;
            },
            isActive(stepNumber) {
                return this.step === stepNumber;
            },
            isCompleted(stepNumber) {
                return this.step > stepNumber;
            },
            stepCircleClass(stepNumber) {
                if (this.isActive(stepNumber)) {
                    return 'bg-primary text-on-primary shadow-lg';
                }

                if (this.isCompleted(stepNumber)) {
                    return 'bg-primary text-on-primary';
                }

                return 'bg-surface-container-highest text-on-surface-variant';
            },
            stepLabelClass(stepNumber) {
                if (this.isActive(stepNumber) || this.isCompleted(stepNumber)) {
                    return 'text-primary';
                }

                return 'text-on-surface-variant';
            },
            validateStep(stepNumber) {
                const panel = this.$refs['stepPanel' + stepNumber];

                if (! panel) {
                    return true;
                }

                const fields = panel.querySelectorAll('input, select, textarea');

                for (const field of fields) {
                    if (! field.checkValidity()) {
                        field.reportValidity();
                        field.focus();

                        return false;
                    }
                }

                return true;
            },
            nextStep() {
                if (! this.validateStep(this.step)) {
                    return;
                }

                if (this.step < this.totalSteps) {
                    this.step++;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            previousStep() {
                if (this.step > 1) {
                    this.step--;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            nextLabel() {
                return this.nextStepLabels[this.step] ?? @js(__('Onboard Clinic'));
            },
            setLogoPreview(event) {
                const file = event.target.files[0] ?? null;
                this.logoPreview = file ? URL.createObjectURL(file) : null;
            },
            handleSubmit(event) {
                for (let stepNumber = 1; stepNumber <= this.totalSteps; stepNumber++) {
                    if (! this.validateStep(stepNumber)) {
                        event.preventDefault();
                        this.step = stepNumber;
                        window.scrollTo({ top: 0, behavior: 'smooth' });

                        return;
                    }
                }
            },
        }"
    >
        <div class="mb-8">
            <a href="{{ route('clinics.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to Clinics') }}
            </a>
            <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Onboard New Clinic') }}</h2>
            <p class="font-body-md text-on-surface-variant">
                {{ __('Create an organization, first branch, domain, and primary admin account.') }}
            </p>
        </div>

        <!-- Progress Stepper -->
        <div class="mb-12">
            <div class="flex items-center justify-between relative">
                <div class="absolute top-1/2 left-0 w-full h-1 bg-surface-container -translate-y-1/2 z-0"></div>
                <div
                    class="absolute top-1/2 left-0 h-1 bg-primary -translate-y-1/2 z-0 transition-all duration-500"
                    :style="'width: ' + progressWidth() + '%'"
                ></div>

                <template x-for="wizardStep in steps" :key="wizardStep.number">
                    <div class="relative z-10 flex flex-col items-center">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors"
                            :class="stepCircleClass(wizardStep.number)"
                        >
                            <span x-show="! isCompleted(wizardStep.number)" x-text="wizardStep.number"></span>
                            <span x-show="isCompleted(wizardStep.number)" class="material-symbols-outlined text-sm">check</span>
                        </div>
                        <span class="mt-2 font-label-md" :class="stepLabelClass(wizardStep.number)" x-text="wizardStep.label"></span>
                    </div>
                </template>
            </div>
        </div>

        <form method="POST" action="{{ route('clinics.store') }}" enctype="multipart/form-data" @submit="handleSubmit">
            @csrf

            <!-- Wizard Card -->
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <!-- Step 1: Clinic Profile -->
                <div x-show="step === 1" x-ref="stepPanel1" style="{{ $stepPanelStyle(1) }}">
                    <div class="p-xl border-b border-outline-variant bg-surface-bright">
                        <h3 class="font-h3 text-on-surface">{{ __('Step 1: Clinic Profile') }}</h3>
                        <p class="text-body-md text-on-surface-variant mt-1">
                            {{ __('Provide the foundational identity details for the new dental facility.') }}
                        </p>
                    </div>

                    <div class="p-xl grid grid-cols-12 gap-lg">
                        <div class="col-span-12 lg:col-span-8 space-y-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                                <div class="space-y-unit">
                                    <label for="name" class="font-label-md text-on-surface-variant block">{{ __('Clinic Name') }} *</label>
                                    <input
                                        id="name"
                                        name="name"
                                        type="text"
                                        value="{{ old('name') }}"
                                        required
                                        placeholder="{{ __('e.g. BrightSmile Dental Group') }}"
                                        class="{{ $inputClass }} @error('name') {{ $inputErrorClass }} @enderror"
                                    />
                                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                </div>

                                <div class="space-y-unit">
                                    <label for="email" class="font-label-md text-on-surface-variant block">{{ __('Primary Contact Email') }}</label>
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email') }}"
                                        placeholder="admin@clinic.com"
                                        class="{{ $inputClass }} @error('email') {{ $inputErrorClass }} @enderror"
                                    />
                                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                                <div class="space-y-unit">
                                    <label for="phone" class="font-label-md text-on-surface-variant block">{{ __('Phone Number') }}</label>
                                    <input
                                        id="phone"
                                        name="phone"
                                        type="tel"
                                        value="{{ old('phone') }}"
                                        placeholder="+1 (555) 000-0000"
                                        class="{{ $inputClass }} @error('phone') {{ $inputErrorClass }} @enderror"
                                    />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                                </div>

                                <div class="space-y-unit">
                                    <label for="address" class="font-label-md text-on-surface-variant block">{{ __('Clinic Address') }}</label>
                                    <input
                                        id="address"
                                        name="address"
                                        type="text"
                                        value="{{ old('address') }}"
                                        placeholder="{{ __('123 Main St, Suite 100') }}"
                                        class="{{ $inputClass }} @error('address') {{ $inputErrorClass }} @enderror"
                                    />
                                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <div class="col-span-12 lg:col-span-4 lg:border-l lg:border-outline-variant lg:pl-lg">
                            <div class="space-y-md">
                                <label for="logo" class="font-label-md text-on-surface-variant block">{{ __('Clinic Logo') }}</label>
                                <label
                                    for="logo"
                                    class="group relative aspect-square w-full border-2 border-dashed rounded-xl flex flex-col items-center justify-center bg-surface-container-low hover:border-primary hover:bg-surface-container transition-all cursor-pointer overflow-hidden"
                                    :class="logoPreview ? 'border-primary' : 'border-outline-variant'"
                                >
                                    <input
                                        id="logo"
                                        name="logo"
                                        type="file"
                                        accept="image/png,image/jpeg"
                                        class="sr-only"
                                        @change="setLogoPreview($event)"
                                    />

                                    <img
                                        x-show="logoPreview"
                                        :src="logoPreview"
                                        alt="{{ __('Clinic logo preview') }}"
                                        class="absolute inset-0 h-full w-full object-cover"
                                    />

                                    <div
                                        x-show="! logoPreview"
                                        class="flex flex-col items-center justify-center px-4 text-center"
                                    >
                                        <span class="material-symbols-outlined text-outline text-4xl mb-2 group-hover:text-primary transition-colors">cloud_upload</span>
                                        <p class="text-label-md text-on-surface-variant">
                                            {{ __('Click or drag clinic logo to upload (PNG, JPG up to 2MB)') }}
                                        </p>
                                    </div>
                                </label>
                                <x-input-error :messages="$errors->get('logo')" class="mt-1" />
                                <div class="flex gap-2">
                                    <span class="material-symbols-outlined text-primary text-sm">info</span>
                                    <p class="text-xs text-on-surface-variant">{{ __('Recommended size: 512x512px') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Branches -->
                <div x-show="step === 2" x-ref="stepPanel2" style="{{ $stepPanelStyle(2) }}">
                    <div class="p-xl border-b border-outline-variant bg-surface-bright">
                        <h3 class="font-h3 text-on-surface">{{ __('Step 2: First Branch') }}</h3>
                        <p class="text-body-md text-on-surface-variant mt-1">
                            {{ __('Configure the initial branch, tenant slug, and access domain.') }}
                        </p>
                    </div>

                    <div class="p-xl space-y-lg max-w-3xl">
                        <div class="space-y-unit">
                            <label for="branch_name" class="font-label-md text-on-surface-variant block">{{ __('Branch Name') }} *</label>
                            <input
                                id="branch_name"
                                name="branch_name"
                                type="text"
                                value="{{ old('branch_name') }}"
                                required
                                placeholder="{{ __('Downtown Clinic') }}"
                                class="{{ $inputClass }} @error('branch_name') {{ $inputErrorClass }} @enderror"
                            />
                            <x-input-error :messages="$errors->get('branch_name')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                            <div class="space-y-unit">
                                <label for="branch_slug" class="font-label-md text-on-surface-variant block">{{ __('Branch Slug') }} *</label>
                                <input
                                    id="branch_slug"
                                    name="branch_slug"
                                    type="text"
                                    value="{{ old('branch_slug') }}"
                                    required
                                    placeholder="downtown"
                                    class="{{ $inputClass }} @error('branch_slug') {{ $inputErrorClass }} @enderror"
                                />
                                <p class="mt-1 text-body-sm text-outline">
                                    {{ __('Used as the tenant identifier. Lowercase letters, numbers, and dashes only.') }}
                                </p>
                                <x-input-error :messages="$errors->get('branch_slug')" class="mt-1" />
                            </div>

                            <div class="space-y-unit">
                                <label for="domain" class="font-label-md text-on-surface-variant block">{{ __('Branch Domain') }} *</label>
                                <input
                                    id="domain"
                                    name="domain"
                                    type="text"
                                    value="{{ old('domain') }}"
                                    required
                                    placeholder="downtown.localhost"
                                    class="{{ $inputClass }} @error('domain') {{ $inputErrorClass }} @enderror"
                                />
                                <p class="mt-1 text-body-sm text-outline">
                                    {{ __('Hostname only — do not include http:// or port numbers (e.g. use downtown.localhost, not downtown.localhost:8000).') }}
                                </p>
                                <x-input-error :messages="$errors->get('domain')" class="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Subscription -->
                <div x-show="step === 3" x-ref="stepPanel3" style="{{ $stepPanelStyle(3) }}">
                    <div class="p-xl border-b border-outline-variant bg-surface-bright">
                        <h3 class="font-h3 text-on-surface">{{ __('Step 3: Subscription Plan') }}</h3>
                        <p class="text-body-md text-on-surface-variant mt-1">
                            {{ __('Choose the organization type that best matches this clinic\'s scale.') }}
                        </p>
                    </div>

                    <div class="p-xl">
                        <input type="hidden" name="type" x-model="organizationType" required />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                            <button
                                type="button"
                                class="relative text-left cursor-pointer rounded-xl border-2 p-lg transition-all hover:border-primary/50"
                                :class="organizationType === 'single' ? 'border-primary bg-primary/5 shadow-md' : 'border-outline-variant bg-white'"
                                @click="organizationType = 'single'"
                            >
                                <div class="flex items-start gap-md">
                                    <div class="p-sm rounded-lg bg-secondary-container">
                                        <span class="material-symbols-outlined text-secondary">storefront</span>
                                    </div>
                                    <div>
                                        <h4 class="font-label-md text-on-surface">{{ __('Professional') }}</h4>
                                        <p class="text-body-sm text-on-surface-variant mt-1">{{ __('Single Practice') }}</p>
                                        <p class="text-body-sm text-outline mt-3">
                                            {{ __('Ideal for independent clinics with one primary location and a focused care team.') }}
                                        </p>
                                    </div>
                                </div>
                            </button>

                            <button
                                type="button"
                                class="relative text-left cursor-pointer rounded-xl border-2 p-lg transition-all hover:border-primary/50"
                                :class="organizationType === 'dso' ? 'border-primary bg-primary/5 shadow-md' : 'border-outline-variant bg-white'"
                                @click="organizationType = 'dso'"
                            >
                                <div class="flex items-start gap-md">
                                    <div class="p-sm rounded-lg bg-primary-fixed">
                                        <span class="material-symbols-outlined text-primary">apartment</span>
                                    </div>
                                    <div>
                                        <h4 class="font-label-md text-on-surface">{{ __('Enterprise') }}</h4>
                                        <p class="text-body-sm text-on-surface-variant mt-1">{{ __('DSO / Multi-location') }}</p>
                                        <p class="text-body-sm text-outline mt-3">
                                            {{ __('Built for dental service organizations managing multiple branches and centralized operations.') }}
                                        </p>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('type')" class="mt-4" />
                    </div>
                </div>

                <!-- Step 4: Primary Admin -->
                <div x-show="step === 4" x-ref="stepPanel4" style="{{ $stepPanelStyle(4) }}">
                    <div class="p-xl border-b border-outline-variant bg-surface-bright">
                        <h3 class="font-h3 text-on-surface">{{ __('Step 4: Primary Admin') }}</h3>
                        <p class="text-body-md text-on-surface-variant mt-1">
                            {{ __('Create the first administrator account for this clinic.') }}
                        </p>
                    </div>

                    <div class="p-xl space-y-lg max-w-3xl">
                        <div class="space-y-unit">
                            <label for="admin_name" class="font-label-md text-on-surface-variant block">{{ __('Admin Name') }} *</label>
                            <input
                                id="admin_name"
                                name="admin_name"
                                type="text"
                                value="{{ old('admin_name') }}"
                                required
                                placeholder="{{ __('Dr. Jane Smith') }}"
                                class="{{ $inputClass }} @error('admin_name') {{ $inputErrorClass }} @enderror"
                            />
                            <x-input-error :messages="$errors->get('admin_name')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                            <div class="space-y-unit">
                                <label for="admin_email" class="font-label-md text-on-surface-variant block">{{ __('Admin Email') }} *</label>
                                <input
                                    id="admin_email"
                                    name="admin_email"
                                    type="email"
                                    value="{{ old('admin_email') }}"
                                    required
                                    placeholder="admin@clinic.com"
                                    class="{{ $inputClass }} @error('admin_email') {{ $inputErrorClass }} @enderror"
                                />
                                <x-input-error :messages="$errors->get('admin_email')" class="mt-1" />
                            </div>

                            <div class="space-y-unit">
                                <label for="admin_password" class="font-label-md text-on-surface-variant block">{{ __('Admin Password') }} *</label>
                                <input
                                    id="admin_password"
                                    name="admin_password"
                                    type="password"
                                    required
                                    class="{{ $inputClass }} @error('admin_password') {{ $inputErrorClass }} @enderror"
                                />
                                <x-input-error :messages="$errors->get('admin_password')" class="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wizard Actions -->
                <div class="p-lg bg-surface-container-low flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-outline-variant">
                    <a
                        href="{{ route('clinics.index') }}"
                        class="px-xl py-md font-label-md text-on-surface-variant bg-transparent border border-outline-variant rounded-lg hover:bg-white transition-colors flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined text-sm">close</span>
                        {{ __('Cancel') }}
                    </a>

                    <div class="flex gap-4">
                        <button
                            type="button"
                            class="px-xl py-md font-label-md text-primary bg-transparent border border-primary rounded-lg hover:bg-primary/5 transition-colors disabled:opacity-50"
                            :disabled="step === 1"
                            @click="previousStep()"
                        >
                            {{ __('Back') }}
                        </button>

                        <button
                            type="button"
                            x-show="step < totalSteps"
                            style="{{ $initialStep < 4 ? '' : 'display: none;' }}"
                            class="px-xl py-md font-label-md text-on-primary bg-primary rounded-lg hover:bg-primary/90 shadow-md transition-all flex items-center gap-2"
                            @click="nextStep()"
                        >
                            <span x-text="nextLabel()">{{ $nextStepLabels[$initialStep] ?? __('Onboard Clinic') }}</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </button>

                        <button
                            type="submit"
                            x-show="step === totalSteps"
                            style="{{ $initialStep === 4 ? '' : 'display: none;' }}"
                            class="px-xl py-md font-label-md text-on-primary bg-primary rounded-lg hover:bg-primary/90 shadow-md transition-all flex items-center gap-2"
                        >
                            {{ __('Onboard Clinic') }}
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Secondary Info / Bento Section -->
        <div class="mt-xl grid grid-cols-1 md:grid-cols-3 gap-lg">
            <div class="bg-blue-50 border border-blue-100 p-lg rounded-xl flex gap-md items-start">
                <div class="bg-blue-600/10 p-sm rounded-lg shrink-0">
                    <span class="material-symbols-outlined text-primary">security</span>
                </div>
                <div>
                    <h4 class="font-label-md text-primary">{{ __('Secure Data Hosting') }}</h4>
                    <p class="text-body-sm text-slate-600 mt-1">
                        {{ __('All patient records are encrypted using AES-256 standard and hosted in HIPAA-compliant zones.') }}
                    </p>
                </div>
            </div>

            <div class="bg-emerald-50 border border-emerald-100 p-lg rounded-xl flex gap-md items-start">
                <div class="bg-emerald-600/10 p-sm rounded-lg shrink-0">
                    <span class="material-symbols-outlined text-secondary">verified_user</span>
                </div>
                <div>
                    <h4 class="font-label-md text-secondary">{{ __('Verified Enterprise') }}</h4>
                    <p class="text-body-sm text-slate-600 mt-1">
                        {{ __('This clinic will be onboarded to the DentaFlow Enterprise network with full auditing capabilities.') }}
                    </p>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-100 p-lg rounded-xl flex gap-md items-start">
                <div class="bg-amber-600/10 p-sm rounded-lg shrink-0">
                    <span class="material-symbols-outlined text-tertiary">support</span>
                </div>
                <div>
                    <h4 class="font-label-md text-tertiary">{{ __('24/7 Deployment Help') }}</h4>
                    <p class="text-body-sm text-slate-600 mt-1">
                        {{ __('Need assistance? Our deployment team is ready to help with branch configuration and data migration.') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="mt-xl">
            <div class="bg-slate-900 rounded-xl p-xl flex flex-col lg:flex-row items-start lg:items-center justify-between gap-lg text-white overflow-hidden relative">
                <div class="relative z-10">
                    <h3 class="font-h3">{{ __('Enterprise Dashboard Preview') }}</h3>
                    <p class="text-slate-400 mt-2 max-w-lg">
                        {{ __('Once completed, the clinic will gain access to the full DentaFlow suite including inventory management, automated scheduling, and real-time analytics.') }}
                    </p>
                </div>
                <div class="relative z-10">
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex bg-white text-slate-900 px-lg py-md rounded-lg font-label-md hover:bg-slate-100 transition-colors"
                    >
                        {{ __('View Demo Dashboard') }}
                    </a>
                </div>
                <div class="absolute right-0 top-0 w-1/3 h-full opacity-20 pointer-events-none hidden lg:block">
                    <div class="absolute inset-0 bg-gradient-to-l from-blue-500 to-transparent"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
