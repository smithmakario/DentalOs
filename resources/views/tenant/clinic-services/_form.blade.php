@php
    $inputClass = 'w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none';
    $iconOptions = [
        'medical_services' => __('General / Checkup'),
        'clean_hands' => __('Cleaning / Hygiene'),
        'auto_awesome' => __('Cosmetic'),
        'emergency' => __('Emergency'),
        'dentistry' => __('Dentistry'),
        'vaccines' => __('Preventive'),
        'healing' => __('Restorative'),
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="code">{{ __('Service Code') }} *</label>
        <input class="{{ $inputClass }} @error('code') border-error @enderror" id="code" name="code" type="text" value="{{ old('code', $service->code) }}" required placeholder="PREV-001" />
        @error('code') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="category">{{ __('Category') }} *</label>
        <input class="{{ $inputClass }} @error('category') border-error @enderror" id="category" name="category" type="text" value="{{ old('category', $service->category) }}" required placeholder="{{ __('Preventive') }}" list="service-categories" />
        <datalist id="service-categories">
            <option value="{{ __('Restorative') }}"></option>
            <option value="{{ __('Endodontics') }}"></option>
            <option value="{{ __('Prosthodontics') }}"></option>
            <option value="{{ __('Oral Surgery') }}"></option>
            <option value="{{ __('Preventive') }}"></option>
            <option value="{{ __('Cosmetic') }}"></option>
            <option value="{{ __('Emergency') }}"></option>
            <option value="{{ __('Orthodontics') }}"></option>
            <option value="{{ __('Diagnostics') }}"></option>
        </datalist>
        @error('category') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block font-label-md text-on-surface-variant mb-2" for="name">{{ __('Service Name') }} *</label>
        <input class="{{ $inputClass }} @error('name') border-error @enderror" id="name" name="name" type="text" value="{{ old('name', $service->name) }}" required />
        @error('name') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block font-label-md text-on-surface-variant mb-2" for="description">{{ __('Description') }}</label>
        <textarea class="{{ $inputClass }} @error('description') border-error @enderror" id="description" name="description" rows="3" placeholder="{{ __('Describe what this service includes for patients.') }}">{{ old('description', $service->description) }}</textarea>
        @error('description') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="price">{{ __('Price (₦)') }} *</label>
        <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-label-md">₦</span>
            <input class="{{ $inputClass }} pl-10 @error('price') border-error @enderror" id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $service->price) }}" required />
        </div>
        @error('price') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="duration_minutes">{{ __('Duration (minutes)') }} *</label>
        <input class="{{ $inputClass }} @error('duration_minutes') border-error @enderror" id="duration_minutes" name="duration_minutes" type="number" min="5" max="480" step="5" value="{{ old('duration_minutes', $service->duration_minutes ?? 30) }}" required />
        @error('duration_minutes') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="icon">{{ __('Icon') }}</label>
        <select class="{{ $inputClass }} @error('icon') border-error @enderror" id="icon" name="icon">
            <option value="">{{ __('Default') }}</option>
            @foreach ($iconOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('icon', $service->icon) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('icon') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="flex flex-col gap-4 pt-2">
        <div class="flex items-center gap-3">
            <input type="hidden" name="is_recommended" value="0" />
            <input class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" id="is_recommended" name="is_recommended" type="checkbox" value="1" @checked(old('is_recommended', $service->is_recommended)) />
            <label class="font-body-md text-on-surface-variant" for="is_recommended">{{ __('Mark as recommended') }}</label>
        </div>

        <div class="flex items-center gap-3">
            <input type="hidden" name="is_active" value="0" />
            <input class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $service->is_active)) />
            <label class="font-body-md text-on-surface-variant" for="is_active">{{ __('Service is active') }}</label>
        </div>
    </div>
</div>
