@php
    use App\Support\AppointmentWizard;

    $progressWidth = match ($wizard['step']) {
        1 => '0%',
        2 => '33%',
        3 => '66%',
        4 => '75%',
        default => '0%',
    };
@endphp

<div class="mb-12">
    @if ($wizard['step'] === 3)
        <div class="flex items-center justify-center max-w-3xl mx-auto">
            @foreach ([1 => __('Services'), 2 => __('Provider'), 3 => __('Time'), 4 => __('Review')] as $stepNumber => $label)
                <div class="flex flex-col items-center flex-1">
                    <div @class([
                        'w-10 h-10 rounded-full flex items-center justify-center mb-2',
                        'bg-secondary-container text-on-secondary-container' => $stepNumber < $wizard['step'],
                        'bg-primary text-on-primary shadow-lg shadow-primary/20' => $stepNumber === $wizard['step'],
                        'border-2 border-outline-variant text-outline' => $stepNumber > $wizard['step'],
                    ])>
                        @if ($stepNumber < $wizard['step'])
                            <span class="material-symbols-outlined text-md">check</span>
                        @else
                            <span class="font-bold">{{ $stepNumber }}</span>
                        @endif
                    </div>
                    <span @class([
                        'font-label-md',
                        'text-on-surface-variant' => $stepNumber < $wizard['step'],
                        'text-primary font-bold' => $stepNumber === $wizard['step'],
                        'text-outline' => $stepNumber > $wizard['step'],
                    ])>{{ $label }}</span>
                </div>
                @if ($stepNumber < 4)
                    <div class="h-px bg-outline-variant flex-grow -mt-6"></div>
                @endif
            @endforeach
        </div>
    @elseif ($wizard['step'] === 4)
        <div class="flex items-center justify-between relative max-w-3xl mx-auto">
            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-surface-container-highest -translate-y-1/2 z-0"></div>
            <div class="absolute top-1/2 left-0 h-0.5 bg-primary -translate-y-1/2 z-0" style="width: {{ $progressWidth }}"></div>
            @foreach ([1 => __('Services'), 2 => __('Provider'), 3 => __('Time'), 4 => __('Review')] as $stepNumber => $label)
                <div class="relative z-10 flex flex-col items-center">
                    <div @class([
                        'w-10 h-10 rounded-full flex items-center justify-center shadow-sm',
                        'bg-secondary text-on-secondary' => $stepNumber < $wizard['step'],
                        'bg-primary text-on-primary ring-4 ring-primary-fixed' => $stepNumber === $wizard['step'],
                    ])>
                        @if ($stepNumber < $wizard['step'])
                            <span class="material-symbols-outlined text-md">check</span>
                        @else
                            <span class="font-bold">{{ $stepNumber }}</span>
                        @endif
                    </div>
                    <span @class([
                        'mt-2 font-label-md',
                        'text-on-surface' => $stepNumber <= $wizard['step'],
                        'text-primary font-bold' => $stepNumber === $wizard['step'],
                    ])>{{ $label }}</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex items-center justify-between max-w-3xl mx-auto relative">
            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-surface-container-high -translate-y-1/2 z-0"></div>
            <div class="absolute top-1/2 left-0 h-0.5 bg-primary -translate-y-1/2 z-0" style="width: {{ $progressWidth }}"></div>
            @foreach ([1 => __('Services'), 2 => __('Provider'), 3 => __('Time'), 4 => __('Review')] as $stepNumber => $label)
                <div class="relative z-10 flex flex-col items-center gap-2">
                    <div @class([
                        'w-10 h-10 rounded-full flex items-center justify-center shadow-md',
                        'bg-primary text-on-primary' => $stepNumber < $wizard['step'],
                        'bg-white border-2 border-primary text-primary shadow-lg' => $stepNumber === $wizard['step'],
                        'bg-surface-container-lowest border-2 border-surface-container-high text-outline' => $stepNumber > $wizard['step'],
                    ])>
                        @if ($stepNumber < $wizard['step'])
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        @else
                            <span class="font-bold">{{ $stepNumber }}</span>
                        @endif
                    </div>
                    <span @class([
                        'font-label-md',
                        'text-primary' => $stepNumber <= $wizard['step'] && $stepNumber !== $wizard['step'],
                        'text-on-surface font-bold' => $stepNumber === $wizard['step'],
                        'text-outline' => $stepNumber > $wizard['step'],
                    ])>{{ $label }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
