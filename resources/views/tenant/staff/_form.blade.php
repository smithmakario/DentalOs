@php
    $inputClass = 'w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2 flex flex-col sm:flex-row gap-6 items-start">
        <div class="flex flex-col items-center gap-3">
            <img
                alt="{{ $member->full_name }}"
                class="w-24 h-24 rounded-2xl object-cover border border-outline-variant shadow-sm"
                src="{{ $member->avatarUrl() }}"
            />
            <label class="cursor-pointer text-primary font-label-md hover:underline">
                {{ __('Upload photo') }}
                <input accept="image/*" class="sr-only" name="avatar" type="file" />
            </label>
            @error('avatar') <p class="text-body-sm text-error">{{ $message }}</p> @enderror
            @if ($member->exists && $member->avatar_path)
                <label class="flex items-center gap-2 text-body-sm text-on-surface-variant">
                    <input class="rounded text-primary focus:ring-primary" name="remove_avatar" type="checkbox" value="1" @checked(old('remove_avatar')) />
                    {{ __('Remove photo') }}
                </label>
            @endif
        </div>

        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
            <div>
                <label class="block font-label-md text-on-surface-variant mb-2" for="first_name">{{ __('First Name') }} *</label>
                <input class="{{ $inputClass }} @error('first_name') border-error @enderror" id="first_name" name="first_name" type="text" value="{{ old('first_name', $member->first_name) }}" required />
                @error('first_name') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block font-label-md text-on-surface-variant mb-2" for="last_name">{{ __('Last Name') }} *</label>
                <input class="{{ $inputClass }} @error('last_name') border-error @enderror" id="last_name" name="last_name" type="text" value="{{ old('last_name', $member->last_name) }}" required />
                @error('last_name') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="email">{{ __('Email') }} *</label>
        <input class="{{ $inputClass }} @error('email') border-error @enderror" id="email" name="email" type="email" value="{{ old('email', $member->email) }}" required />
        @error('email') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="phone">{{ __('Phone') }}</label>
        <input class="{{ $inputClass }} @error('phone') border-error @enderror" id="phone" name="phone" type="text" value="{{ old('phone', $member->phone) }}" />
        @error('phone') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="role">{{ __('Role') }} *</label>
        <select class="{{ $inputClass }} @error('role') border-error @enderror" id="role" name="role" required>
            @foreach ($roles as $roleOption)
                <option value="{{ $roleOption->value }}" @selected(old('role', $member->role?->value) === $roleOption->value)>{{ $roleOption->label() }}</option>
            @endforeach
        </select>
        @error('role') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="specialization">{{ __('Specialty') }}</label>
        <input class="{{ $inputClass }} @error('specialization') border-error @enderror" id="specialization" name="specialization" type="text" value="{{ old('specialization', $member->specialization) }}" placeholder="{{ __('e.g. Orthodontics, Pediatric Dentistry') }}" list="specialty-suggestions" />
        <datalist id="specialty-suggestions">
            <option value="{{ __('General Dentistry') }}"></option>
            <option value="{{ __('Orthodontics') }}"></option>
            <option value="{{ __('Pediatric Dentistry') }}"></option>
            <option value="{{ __('Oral Surgery') }}"></option>
            <option value="{{ __('Periodontics') }}"></option>
            <option value="{{ __('Endodontics') }}"></option>
            <option value="{{ __('Prosthodontics') }}"></option>
        </datalist>
        @error('specialization') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="license_number">{{ __('License Number') }}</label>
        <input class="{{ $inputClass }} @error('license_number') border-error @enderror" id="license_number" name="license_number" type="text" value="{{ old('license_number', $member->license_number) }}" />
        @error('license_number') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="years_of_experience">{{ __('Years of Experience') }}</label>
        <input class="{{ $inputClass }} @error('years_of_experience') border-error @enderror" id="years_of_experience" name="years_of_experience" type="number" min="0" max="60" value="{{ old('years_of_experience', $member->years_of_experience) }}" />
        @error('years_of_experience') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="password">
            {{ $member->exists ? __('New Password (optional)') : __('Password') }}
            @unless ($member->exists) * @endunless
        </label>
        <input class="{{ $inputClass }} @error('password') border-error @enderror" id="password" name="password" type="password" @unless($member->exists) required @endunless autocomplete="new-password" />
        @error('password') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-3 pt-8">
        <input name="is_active" type="hidden" value="0" />
        <input class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $member->is_active)) @disabled($member->exists && $member->id === auth('staff')->id()) />
        <label class="font-body-md text-on-surface-variant" for="is_active">{{ __('Active staff member') }}</label>
    </div>
</div>
