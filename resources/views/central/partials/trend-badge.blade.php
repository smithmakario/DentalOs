@php
    $direction = $trend['direction'] ?? 'flat';
    $value = $trend['value'] ?? 0;
    $colorClass = match ($direction) {
        'up' => 'text-green-600',
        'down' => 'text-red-600',
        default => 'text-outline',
    };
    $icon = match ($direction) {
        'up' => 'trending_up',
        'down' => 'trending_down',
        default => 'remove',
    };
@endphp

<span class="{{ $colorClass }} flex items-center font-label-sm">
    <span class="material-symbols-outlined text-xs" data-icon="{{ $icon }}">{{ $icon }}</span>
    {{ number_format($value, 1) }}%
</span>
