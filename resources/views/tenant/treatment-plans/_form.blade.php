@php
    $inputClass = 'w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none';
    $serviceOptions = $clinicServices->map(fn ($service) => [
        'id' => $service->id,
        'code' => $service->code,
        'name' => $service->name,
        'price' => $service->price,
    ])->values();

    $options = old('options', $treatmentPlan->relationLoaded('options') && $treatmentPlan->options->isNotEmpty()
        ? $treatmentPlan->options->map(fn ($option) => [
            'name' => $option->name,
            'description' => $option->description,
            'items' => $option->items->map(fn ($item) => [
                'clinic_service_id' => $item->clinic_service_id,
                'name' => $item->name,
                'procedure_code' => $item->procedure_code,
                'description' => $item->description,
                'tooth_code' => $item->tooth_code,
                'surface' => $item->surface,
                'phase_name' => $item->phase_name,
                'phase_order' => $item->phase_order,
                'estimated_cost' => $item->estimated_cost,
            ])->all(),
        ])->all()
        : [[
            'name' => __('Option A'),
            'description' => '',
            'items' => [[
                'clinic_service_id' => '',
                'name' => '',
                'procedure_code' => '',
                'description' => '',
                'tooth_code' => '',
                'surface' => '',
                'phase_name' => __('Phase 1'),
                'phase_order' => 0,
                'estimated_cost' => '',
            ]],
        ]]);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="patient_id">{{ __('Patient') }} *</label>
        <select class="{{ $inputClass }} @error('patient_id') border-error @enderror" id="patient_id" name="patient_id" required>
            <option value="">{{ __('Select patient') }}</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" @selected((int) old('patient_id', $treatmentPlan->patient_id) === $patient->id)>{{ $patient->full_name }}</option>
            @endforeach
        </select>
        @error('patient_id') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="provider_id">{{ __('Provider') }} *</label>
        <select class="{{ $inputClass }} @error('provider_id') border-error @enderror" id="provider_id" name="provider_id" required>
            <option value="">{{ __('Select provider') }}</option>
            @foreach ($providers as $provider)
                <option value="{{ $provider->id }}" @selected((int) old('provider_id', $treatmentPlan->provider_id) === $provider->id)>{{ $provider->full_name }}</option>
            @endforeach
        </select>
        @error('provider_id') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block font-label-md text-on-surface-variant mb-2" for="title">{{ __('Case Title') }} *</label>
        <input class="{{ $inputClass }} @error('title') border-error @enderror" id="title" name="title" type="text" value="{{ old('title', $treatmentPlan->title) }}" required placeholder="{{ __('e.g. Missing tooth #19 restoration') }}" />
        @error('title') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block font-label-md text-on-surface-variant mb-2" for="description">{{ __('Clinical Notes') }}</label>
        <textarea class="{{ $inputClass }} @error('description') border-error @enderror" id="description" name="description" rows="3">{{ old('description', $treatmentPlan->description) }}</textarea>
        @error('description') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="status">{{ __('Status') }} *</label>
        <select class="{{ $inputClass }} @error('status') border-error @enderror" id="status" name="status" required>
            @foreach (\App\Enums\TreatmentPlanStatus::cases() as $planStatus)
                <option value="{{ $planStatus->value }}" @selected(old('status', $treatmentPlan->status?->value ?? 'draft') === $planStatus->value)>{{ ucfirst($planStatus->value) }}</option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-8 border-t border-slate-100 pt-8">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="font-h3 text-on-surface">{{ __('Treatment Options') }}</h3>
            <p class="text-body-sm text-on-surface-variant mt-1">{{ __('Build multiple options (e.g. Implant vs Bridge) with phased procedures and costs.') }}</p>
        </div>
        <button type="button" id="add-option-block" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline">
            <span class="material-symbols-outlined text-sm">add</span>
            {{ __('Add Option') }}
        </button>
    </div>

    @error('options') <p class="mb-4 text-body-sm text-error">{{ $message }}</p> @enderror

    <div id="option-blocks" class="space-y-6" data-services='@json($serviceOptions)'>
        @foreach ($options as $optionIndex => $option)
            <div class="option-block border border-slate-200 rounded-xl p-5 bg-slate-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Option Name') }} *</label>
                        <input class="{{ $inputClass }} @error("options.{$optionIndex}.name") border-error @enderror" name="options[{{ $optionIndex }}][name]" type="text" value="{{ $option['name'] ?? '' }}" placeholder="{{ __('Option A: Dental Implant') }}" />
                        @error("options.{$optionIndex}.name") <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Option Summary') }}</label>
                        <input class="{{ $inputClass }}" name="options[{{ $optionIndex }}][description]" type="text" value="{{ $option['description'] ?? '' }}" />
                    </div>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-label-md text-on-surface">{{ __('Phased Procedures') }}</h4>
                    <button type="button" class="add-item-row text-primary text-sm hover:underline">{{ __('Add Procedure') }}</button>
                </div>

                @error("options.{$optionIndex}.items") <p class="mb-3 text-body-sm text-error">{{ $message }}</p> @enderror

                <div class="item-rows space-y-3">
                    @foreach ($option['items'] as $itemIndex => $item)
                        <div class="item-row grid grid-cols-1 md:grid-cols-8 gap-3 p-3 bg-white rounded-lg border border-slate-200">
                            <div class="md:col-span-2">
                                <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Catalog Service') }}</label>
                                <select class="{{ $inputClass }} service-picker" name="options[{{ $optionIndex }}][items][{{ $itemIndex }}][clinic_service_id]">
                                    <option value="">{{ __('Custom procedure') }}</option>
                                    @foreach ($clinicServices as $service)
                                        <option value="{{ $service->id }}" data-code="{{ $service->code }}" data-name="{{ $service->name }}" data-price="{{ $service->price }}" @selected((string) ($item['clinic_service_id'] ?? '') === (string) $service->id)>{{ $service->name }} ({{ $service->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Procedure') }} *</label>
                                <input class="{{ $inputClass }} item-name @error("options.{$optionIndex}.items.{$itemIndex}.name") border-error @enderror" name="options[{{ $optionIndex }}][items][{{ $itemIndex }}][name]" type="text" value="{{ $item['name'] ?? '' }}" />
                                @error("options.{$optionIndex}.items.{$itemIndex}.name") <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Code') }}</label>
                                <input class="{{ $inputClass }} item-code" name="options[{{ $optionIndex }}][items][{{ $itemIndex }}][procedure_code]" type="text" value="{{ $item['procedure_code'] ?? '' }}" />
                            </div>
                            <div>
                                <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Phase') }}</label>
                                <input class="{{ $inputClass }}" name="options[{{ $optionIndex }}][items][{{ $itemIndex }}][phase_name]" type="text" value="{{ $item['phase_name'] ?? __('Phase 1') }}" />
                                <input type="hidden" name="options[{{ $optionIndex }}][items][{{ $itemIndex }}][phase_order]" value="{{ $item['phase_order'] ?? 0 }}" />
                            </div>
                            <div>
                                <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Cost') }}</label>
                                <input class="{{ $inputClass }} item-cost" name="options[{{ $optionIndex }}][items][{{ $itemIndex }}][estimated_cost]" type="number" min="0" step="0.01" value="{{ $item['estimated_cost'] ?? '' }}" />
                            </div>
                            <div class="flex items-end">
                                <button type="button" class="remove-item-row w-full py-2 border border-slate-200 rounded-lg text-sm text-on-surface-variant hover:bg-slate-50" @if(count($option['items']) === 1) disabled @endif>{{ __('Remove') }}</button>
                            </div>
                            <div class="md:col-span-8 grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Tooth') }}</label>
                                    <input class="{{ $inputClass }}" name="options[{{ $optionIndex }}][items][{{ $itemIndex }}][tooth_code]" type="text" value="{{ $item['tooth_code'] ?? '' }}" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Notes') }}</label>
                                    <input class="{{ $inputClass }}" name="options[{{ $optionIndex }}][items][{{ $itemIndex }}][description]" type="text" value="{{ $item['description'] ?? '' }}" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="button" class="remove-option-block text-error text-sm hover:underline" @if(count($options) === 1) disabled @endif>{{ __('Remove Option') }}</button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const optionsContainer = document.getElementById('option-blocks');

        function reindexOptions() {
            optionsContainer.querySelectorAll('.option-block').forEach((block, optionIndex) => {
                block.querySelectorAll('[name^="options["]').forEach((input) => {
                    input.name = input.name.replace(/options\[\d+\]/, `options[${optionIndex}]`);
                });
                block.querySelectorAll('.item-row').forEach((row, itemIndex) => {
                    row.querySelectorAll('[name*="[items]["]').forEach((input) => {
                        input.name = input.name.replace(/\[items\]\[\d+\]/, `[items][${itemIndex}]`);
                    });
                });
                block.querySelector('.remove-option-block').disabled = optionsContainer.querySelectorAll('.option-block').length === 1;
            });
        }

        function bindServicePicker(select) {
            select.addEventListener('change', function () {
                const option = select.selectedOptions[0];
                const row = select.closest('.item-row');
                if (!option || !row || !option.value) return;
                row.querySelector('.item-name').value = option.dataset.name || '';
                row.querySelector('.item-code').value = option.dataset.code || '';
                row.querySelector('.item-cost').value = option.dataset.price || '';
            });
        }

        optionsContainer.querySelectorAll('.service-picker').forEach(bindServicePicker);

        document.getElementById('add-option-block')?.addEventListener('click', function () {
            const template = optionsContainer.querySelector('.option-block').cloneNode(true);
            template.querySelectorAll('input').forEach((input) => {
                if (input.type !== 'hidden') input.value = '';
            });
            template.querySelectorAll('select').forEach((select) => select.selectedIndex = 0);
            optionsContainer.appendChild(template);
            template.querySelectorAll('.service-picker').forEach(bindServicePicker);
            reindexOptions();
        });

        optionsContainer.addEventListener('click', function (event) {
            if (event.target.closest('.add-item-row')) {
                const block = event.target.closest('.option-block');
                const row = block.querySelector('.item-row').cloneNode(true);
                row.querySelectorAll('input').forEach((input) => {
                    if (input.type !== 'hidden') input.value = '';
                });
                row.querySelectorAll('select').forEach((select) => select.selectedIndex = 0);
                block.querySelector('.item-rows').appendChild(row);
                row.querySelectorAll('.service-picker').forEach(bindServicePicker);
                reindexOptions();
            }

            if (event.target.closest('.remove-item-row') && !event.target.disabled) {
                event.target.closest('.item-row')?.remove();
                reindexOptions();
            }

            if (event.target.closest('.remove-option-block') && !event.target.disabled) {
                event.target.closest('.option-block')?.remove();
                reindexOptions();
            }
        });

        reindexOptions();
    });
</script>
