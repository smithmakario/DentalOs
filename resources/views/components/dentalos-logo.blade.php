@props([
    'size' => 'md',
    'showText' => true,
])

@php
    $sizes = [
        'sm' => ['icon' => 'h-8 w-8', 'text' => 'text-lg'],
        'md' => ['icon' => 'h-10 w-10', 'text' => 'text-xl'],
        'lg' => ['icon' => 'h-12 w-12', 'text' => 'text-2xl'],
    ];
    $s = $sizes[$size] ?? $sizes['md'];
@endphp

<a {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 group']) }} href="{{ url('/') }}">
    <img
        src="{{ asset('images/dentalos-logo.svg') }}"
        alt="DentalOS Logo"
        class="{{ $s['icon'] }} shrink-0 transition-transform group-hover:scale-105"
        width="64"
        height="auto"
    />
    @if ($showText)
        <span class="{{ $s['text'] }} font-bold tracking-tight text-on-surface">
            Dental<span class="text-primary">Os</span>
        </span>
    @endif
</a>
