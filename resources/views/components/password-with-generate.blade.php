@props([
    'id' => 'password',
    'name' => 'password',
    'confirmationId' => null,
    'confirmationName' => null,
    'label' => null,
    'confirmationLabel' => null,
    'required' => false,
    'withConfirmation' => false,
    'hint' => null,
    'inputClass' => 'w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none font-body-md',
    'errorBag' => null,
    'value' => '',
    'confirmationValue' => '',
    'length' => 16,
])

@php
    $confirmationId ??= $id.'_confirmation';
    $confirmationName ??= $name.'_confirmation';
    $label ??= __('Password');
    $confirmationLabel ??= __('Confirm password');
    $errorBag ??= $name;
@endphp

<div
    @class(['space-y-4' => $withConfirmation])
    x-data="{
        password: @js(old($name, $value)),
        confirmation: @js(old($confirmationName, $confirmationValue)),
        show: false,
        length: {{ (int) $length }},
        generate() {
            const chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
            const values = new Uint32Array(this.length);
            crypto.getRandomValues(values);
            let result = '';
            for (let i = 0; i < this.length; i++) {
                result += chars[values[i] % chars.length];
            }
            this.password = result;
            this.confirmation = result;
            this.show = true;
        },
    }"
>
    <div>
        <div class="flex items-center justify-between gap-3 mb-2">
            <label for="{{ $id }}" class="block font-label-md text-on-surface-variant">
                {{ $label }}
                @if ($required)
                    *
                @endif
            </label>
            <button
                type="button"
                class="inline-flex items-center gap-1 text-sm font-label-md text-primary hover:underline shrink-0"
                @click="generate()"
            >
                <span class="material-symbols-outlined text-[16px]">autorenew</span>
                {{ __('Generate password') }}
            </button>
        </div>
        <div class="relative">
            <input
                id="{{ $id }}"
                name="{{ $name }}"
                :type="show ? 'text' : 'password'"
                x-model="password"
                autocomplete="new-password"
                @if ($required) required @endif
                {{ $attributes->class([$inputClass, 'pr-12']) }}
            />
            <button
                type="button"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-on-surface-variant hover:text-primary"
                @click="show = ! show"
                :aria-label="show ? @js(__('Hide password')) : @js(__('Show password'))"
            >
                <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
            </button>
        </div>
        @if ($hint)
            <p class="mt-1 text-xs text-on-surface-variant">{{ $hint }}</p>
        @endif
        <x-input-error :messages="$errors->get($errorBag)" class="mt-2" />
    </div>

    @if ($withConfirmation)
        <div>
            <label for="{{ $confirmationId }}" class="block font-label-md text-on-surface-variant mb-2">{{ $confirmationLabel }}</label>
            <input
                id="{{ $confirmationId }}"
                name="{{ $confirmationName }}"
                :type="show ? 'text' : 'password'"
                x-model="confirmation"
                autocomplete="new-password"
                class="{{ $inputClass }}"
            />
        </div>
    @endif
</div>
