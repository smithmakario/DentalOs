<x-tenant-layout>
    <div class="p-8 max-w-6xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-start mb-8">
            <div>
                <a class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4" href="{{ route('tenant.treatment-plans.index') }}">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    {{ __('Back to treatment plans') }}
                </a>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ $treatmentPlan->title }}</h2>
                <p class="font-body-md text-on-surface-variant">
                    <a class="text-primary hover:underline" href="{{ route('tenant.patients.show', $treatmentPlan->patient) }}">{{ $treatmentPlan->patient->full_name }}</a>
                    · {{ $treatmentPlan->provider->full_name }}
                </p>
            </div>
            <div class="flex gap-3">
                @can('update', $treatmentPlan)
                    <a class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90" href="{{ route('tenant.treatment-plans.edit', $treatmentPlan) }}">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        {{ __('Edit Plan') }}
                    </a>
                @endcan
            </div>
        </div>

        @unless ($treatmentPlan->canBeginProcedures())
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg text-body-md text-amber-900">
                <span class="material-symbols-outlined text-sm align-middle mr-1">warning</span>
                {{ __('Digital informed consent is required on a selected treatment option before procedures can begin.') }}
            </div>
        @endunless

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Status') }}</div>
                <div class="text-xl font-bold text-on-surface">{{ ucfirst($treatmentPlan->status->value) }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Options') }}</div>
                <div class="text-xl font-bold text-on-surface">{{ $treatmentPlan->options->count() }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Lowest Estimate') }}</div>
                <div class="text-xl font-bold text-on-surface">{{ number_format($treatmentPlan->estimated_total, 2) }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-label-sm text-on-surface-variant uppercase mb-1">{{ __('Consent') }}</div>
                <div class="text-xl font-bold text-on-surface">{{ $treatmentPlan->hasConsentedOption() ? __('Signed') : __('Pending') }}</div>
            </div>
        </div>

        @if ($treatmentPlan->description)
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6 mb-6">
                <h3 class="font-h3 text-on-surface mb-3">{{ __('Clinical Notes') }}</h3>
                <p class="text-body-md text-on-surface whitespace-pre-line">{{ $treatmentPlan->description }}</p>
            </div>
        @endif

        <div class="space-y-6">
            @foreach ($treatmentPlan->options as $option)
                <div class="bg-white border {{ $option->is_selected ? 'border-primary ring-1 ring-primary/20' : 'border-slate-200' }} rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-start gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-h3 text-on-surface">{{ $option->name }}</h3>
                                @if ($option->is_selected)
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-primary/10 text-primary border border-primary/20">{{ __('Selected') }}</span>
                                @endif
                                @if ($option->hasConsent())
                                    <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">{{ __('Consent Signed') }}</span>
                                @endif
                            </div>
                            @if ($option->description)
                                <p class="text-body-sm text-on-surface-variant">{{ $option->description }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-label-sm text-on-surface-variant uppercase">{{ __('Estimated Total') }}</div>
                            <div class="text-2xl font-bold text-on-surface">{{ number_format($option->estimated_total, 2) }}</div>
                        </div>
                    </div>

                    @php
                        $phases = $option->items->groupBy(fn ($item) => ($item->phase_name ?: __('Phase 1')).'|'.$item->phase_order)->sortKeys();
                    @endphp

                    @foreach ($phases as $phaseKey => $phaseItems)
                        @php [$phaseName] = explode('|', (string) $phaseKey, 2); @endphp
                        <div class="border-b border-slate-100 last:border-b-0">
                            <div class="px-6 py-3 bg-slate-50 font-label-md text-on-surface">{{ $phaseName }}</div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="border-b border-slate-100">
                                            <th class="px-6 py-3 text-label-sm text-on-surface-variant">{{ __('Procedure') }}</th>
                                            <th class="px-6 py-3 text-label-sm text-on-surface-variant">{{ __('Code') }}</th>
                                            <th class="px-6 py-3 text-label-sm text-on-surface-variant">{{ __('Tooth') }}</th>
                                            <th class="px-6 py-3 text-label-sm text-on-surface-variant text-right">{{ __('Cost') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($phaseItems as $item)
                                            <tr>
                                                <td class="px-6 py-3">
                                                    <div class="font-medium text-on-surface">{{ $item->name }}</div>
                                                    @if ($item->description)
                                                        <div class="text-body-sm text-outline">{{ $item->description }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-3 text-body-sm">{{ $item->procedure_code ?: '—' }}</td>
                                                <td class="px-6 py-3 text-body-sm">{{ $item->tooth_code ?: '—' }}</td>
                                                <td class="px-6 py-3 text-right font-label-md">{{ number_format($item->estimated_cost, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    <div class="p-6 bg-slate-50/50">
                        @if ($option->hasConsent())
                            <h4 class="font-label-md text-on-surface mb-3">{{ __('Digital Informed Consent') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-body-sm text-on-surface-variant mb-2">{{ __('Signed by') }}: <strong>{{ $option->consent_signer_name }}</strong></p>
                                    <p class="text-body-sm text-on-surface-variant mb-2">{{ __('Signed at') }}: {{ $option->consent_signed_at->format('M j, Y g:i A') }}</p>
                                    @if ($option->consentWitness)
                                        <p class="text-body-sm text-on-surface-variant mb-2">{{ __('Witnessed by') }}: {{ $option->consentWitness->full_name }}</p>
                                    @endif
                                    <p class="text-body-sm text-on-surface whitespace-pre-line mt-3">{{ $option->consent_statement }}</p>
                                    @can('create', App\Models\Invoice::class)
                                        <a class="inline-flex items-center gap-2 mt-4 bg-primary text-on-primary px-4 py-2 rounded-lg font-label-md hover:opacity-90" href="{{ route('tenant.invoices.create', ['treatment_plan_option_id' => $option->id]) }}">
                                            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                                            {{ __('Create Invoice from Option') }}
                                        </a>
                                    @endcan
                                </div>
                                <div class="bg-white border border-slate-200 rounded-lg p-4">
                                    <img alt="{{ __('Patient signature') }}" class="max-h-40 mx-auto" src="{{ route('tenant.treatment-plans.options.consent-signature', [$treatmentPlan, $option]) }}" />
                                </div>
                            </div>
                        @else
                            @can('signConsent', $option)
                                <h4 class="font-label-md text-on-surface mb-2">{{ __('Capture Digital Informed Consent') }}</h4>
                                <p class="text-body-sm text-on-surface-variant mb-4">{{ __('Record the patient signature for this specific treatment option before starting procedures.') }}</p>

                                <form method="POST" action="{{ route('tenant.treatment-plans.options.consent', [$treatmentPlan, $option]) }}" class="space-y-4" data-consent-form>
                                    @csrf
                                    <div>
                                        <label class="block font-label-md text-on-surface-variant mb-2">{{ __('Patient / Guardian Name') }}</label>
                                        <input class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg" name="consent_signer_name" type="text" value="{{ old('consent_signer_name', $treatmentPlan->patient->full_name) }}" required />
                                    </div>

                                    <div>
                                        <label class="block font-label-md text-on-surface-variant mb-2">{{ __('Consent Statement') }}</label>
                                        <textarea class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg" name="consent_statement" rows="4" required>{{ old('consent_statement', $consentStatement) }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block font-label-md text-on-surface-variant mb-2">{{ __('Signature') }}</label>
                                        <canvas class="consent-canvas w-full h-40 bg-white border border-slate-300 rounded-lg touch-none" width="600" height="160"></canvas>
                                        <input type="hidden" name="consent_signature" class="consent-signature-input" required />
                                        <button type="button" class="clear-signature mt-2 text-sm text-on-surface-variant hover:text-primary">{{ __('Clear signature') }}</button>
                                    </div>

                                    <label class="flex items-start gap-2">
                                        <input class="mt-1" name="consent_acknowledged" type="checkbox" value="1" required />
                                        <span class="text-body-sm text-on-surface-variant">{{ __('The patient (or legal guardian) has reviewed this treatment option and agrees to proceed.') }}</span>
                                    </label>

                                    <button class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90" type="submit">
                                        <span class="material-symbols-outlined text-[18px]">draw</span>
                                        {{ __('Record Consent for :option', ['option' => $option->name]) }}
                                    </button>
                                </form>
                            @endcan
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @can('delete', $treatmentPlan)
            <form method="POST" action="{{ route('tenant.treatment-plans.destroy', $treatmentPlan) }}" class="mt-6" onsubmit="return confirm(@js(__('Delete this treatment plan?')))">
                @csrf
                @method('DELETE')
                <button class="text-error font-label-md hover:underline" type="submit">{{ __('Delete Treatment Plan') }}</button>
            </form>
        @endcan
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-consent-form]').forEach((form) => {
                const canvas = form.querySelector('.consent-canvas');
                const hiddenInput = form.querySelector('.consent-signature-input');
                const clearButton = form.querySelector('.clear-signature');
                const ctx = canvas.getContext('2d');
                let drawing = false;

                ctx.strokeStyle = '#1e293b';
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';

                function pointerPosition(event) {
                    const rect = canvas.getBoundingClientRect();
                    const clientX = event.touches ? event.touches[0].clientX : event.clientX;
                    const clientY = event.touches ? event.touches[0].clientY : event.clientY;
                    return {
                        x: (clientX - rect.left) * (canvas.width / rect.width),
                        y: (clientY - rect.top) * (canvas.height / rect.height),
                    };
                }

                function startDraw(event) {
                    drawing = true;
                    const pos = pointerPosition(event);
                    ctx.beginPath();
                    ctx.moveTo(pos.x, pos.y);
                    event.preventDefault();
                }

                function draw(event) {
                    if (!drawing) return;
                    const pos = pointerPosition(event);
                    ctx.lineTo(pos.x, pos.y);
                    ctx.stroke();
                    event.preventDefault();
                }

                function endDraw() {
                    drawing = false;
                }

                canvas.addEventListener('mousedown', startDraw);
                canvas.addEventListener('mousemove', draw);
                canvas.addEventListener('mouseup', endDraw);
                canvas.addEventListener('mouseleave', endDraw);
                canvas.addEventListener('touchstart', startDraw, { passive: false });
                canvas.addEventListener('touchmove', draw, { passive: false });
                canvas.addEventListener('touchend', endDraw);

                clearButton?.addEventListener('click', function () {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    hiddenInput.value = '';
                });

                form.addEventListener('submit', function (event) {
                    hiddenInput.value = canvas.toDataURL('image/png');
                    if (!hiddenInput.value || hiddenInput.value.length < 100) {
                        event.preventDefault();
                        alert(@js(__('Please provide a signature before submitting.')));
                    }
                });
            });
        });
    </script>
</x-tenant-layout>
