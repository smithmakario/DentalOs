@php
    $inputClass = 'w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none';
    $inputErrorClass = 'border-error';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method ?? false)
        @method($method)
    @endif

    <div>
        <label for="name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Plan Name') }} *</label>
        <input id="name" name="name" type="text" value="{{ old('name', $plan->name) }}" required
            class="{{ $inputClass }} @error('name') {{ $inputErrorClass }} @enderror" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <label for="slug" class="block font-label-md text-on-surface-variant mb-2">{{ __('Slug') }} *</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $plan->slug) }}" required
            class="{{ $inputClass }} @error('slug') {{ $inputErrorClass }} @enderror" />
        <p class="mt-1 text-body-sm text-outline">{{ __('Unique identifier used internally. Lowercase letters, numbers, and dashes only.') }}</p>
        <x-input-error :messages="$errors->get('slug')" class="mt-2" />
    </div>

    <div>
        <label for="organization_type" class="block font-label-md text-on-surface-variant mb-2">{{ __('Organization Type') }} *</label>
        <select id="organization_type" name="organization_type" required
            class="{{ $inputClass }} @error('organization_type') {{ $inputErrorClass }} @enderror">
            @foreach ($organizationTypes as $type)
                <option value="{{ $type->value }}" @selected(old('organization_type', $plan->organization_type?->value) === $type->value)>
                    {{ $type->subscriptionTierLabel() }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('organization_type')" class="mt-2" />
    </div>

    <div>
        <label for="description" class="block font-label-md text-on-surface-variant mb-2">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="3"
            class="{{ $inputClass }} @error('description') {{ $inputErrorClass }} @enderror">{{ old('description', $plan->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="price_monthly" class="block font-label-md text-on-surface-variant mb-2">{{ __('Monthly Price (₦)') }} *</label>
            <input id="price_monthly" name="price_monthly" type="number" min="0" step="0.01" value="{{ old('price_monthly', $plan->price_monthly) }}" required
                class="{{ $inputClass }} @error('price_monthly') {{ $inputErrorClass }} @enderror" />
            <x-input-error :messages="$errors->get('price_monthly')" class="mt-2" />
        </div>
        <div>
            <label for="price_yearly" class="block font-label-md text-on-surface-variant mb-2">{{ __('Yearly Price (₦)') }}</label>
            <input id="price_yearly" name="price_yearly" type="number" min="0" step="0.01" value="{{ old('price_yearly', $plan->price_yearly) }}"
                class="{{ $inputClass }} @error('price_yearly') {{ $inputErrorClass }} @enderror" />
            <x-input-error :messages="$errors->get('price_yearly')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="max_branches" class="block font-label-md text-on-surface-variant mb-2">{{ __('Maximum Branches') }}</label>
            <input id="max_branches" name="max_branches" type="number" min="1" value="{{ old('max_branches', $plan->max_branches) }}"
                class="{{ $inputClass }} @error('max_branches') {{ $inputErrorClass }} @enderror" />
            <p class="mt-1 text-body-sm text-outline">{{ __('Leave empty for unlimited branches.') }}</p>
            <x-input-error :messages="$errors->get('max_branches')" class="mt-2" />
        </div>
        <div>
            <label for="sort_order" class="block font-label-md text-on-surface-variant mb-2">{{ __('Sort Order') }}</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $plan->sort_order ?? 0) }}"
                class="{{ $inputClass }} @error('sort_order') {{ $inputErrorClass }} @enderror" />
            <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
        </div>
    </div>

    <div class="flex items-center gap-3">
        <input type="hidden" name="is_active" value="0" />
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $plan->is_active ?? true))
            class="rounded border-outline-variant text-primary focus:ring-primary" />
        <label for="is_active" class="font-body-md text-on-surface">{{ __('Plan is active and available for subscription') }}</label>
    </div>

    <div class="flex gap-4 pt-2">
        <button type="submit" class="px-6 py-3 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('subscription-plans.index') }}" class="px-6 py-3 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-container-low transition-colors">
            {{ __('Cancel') }}
        </a>
    </div>
</form>
