@props([
    'id' => 'password',
    'name' => 'password',
    'autocomplete' => 'current-password',
])

<div class="relative" x-data="{ show: false }">
    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="lock">lock</span>
    <input
        {{ $attributes->class([
            'w-full pl-10 pr-12 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md',
        ]) }}
        id="{{ $id }}"
        name="{{ $name }}"
        :type="show ? 'text' : 'password'"
        placeholder="••••••••"
        required
        autocomplete="{{ $autocomplete }}"
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
