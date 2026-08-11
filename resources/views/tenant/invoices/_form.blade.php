@php
    $inputClass = 'w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md text-on-surface focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none';
    $serviceOptions = $clinicServices->map(fn ($service) => [
        'id' => $service->id,
        'code' => $service->code,
        'name' => $service->name,
        'price' => $service->price,
    ])->values();

    $items = old('items', isset($prefillItems)
        ? $prefillItems
        : ($invoice->relationLoaded('items') && $invoice->items->isNotEmpty()
            ? $invoice->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->all()
            : [['description' => '', 'quantity' => 1, 'unit_price' => '']]));
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="patient_id">{{ __('Patient') }} *</label>
        <select class="{{ $inputClass }} @error('patient_id') border-error @enderror" id="patient_id" name="patient_id" required>
            <option value="">{{ __('Select patient') }}</option>
            @foreach ($patients as $patient)
                <option value="{{ $patient->id }}" @selected((int) old('patient_id', $invoice->patient_id) === $patient->id)>{{ $patient->full_name }}</option>
            @endforeach
        </select>
        @error('patient_id') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="appointment_id">{{ __('Linked Appointment') }}</label>
        <select class="{{ $inputClass }} @error('appointment_id') border-error @enderror" id="appointment_id" name="appointment_id">
            <option value="">{{ __('None') }}</option>
            @foreach ($appointments as $appointment)
                <option value="{{ $appointment->id }}" @selected((int) old('appointment_id', $invoice->appointment_id) === $appointment->id)>
                    {{ $appointment->patient->full_name }} · {{ $appointment->scheduled_at->format('M j, Y') }}
                </option>
            @endforeach
        </select>
        @error('appointment_id') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="status">{{ __('Status') }} *</label>
        <select class="{{ $inputClass }} @error('status') border-error @enderror" id="status" name="status" required>
            @foreach (\App\Enums\InvoiceStatus::cases() as $invoiceStatus)
                @continue(in_array($invoiceStatus, [\App\Enums\InvoiceStatus::Partial, \App\Enums\InvoiceStatus::Paid], true))
                <option value="{{ $invoiceStatus->value }}" @selected(old('status', $invoice->status?->value ?? 'draft') === $invoiceStatus->value)>{{ $invoiceStatus->label() }}</option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block font-label-md text-on-surface-variant mb-2" for="issued_at">{{ __('Issue Date') }}</label>
            <input class="{{ $inputClass }} @error('issued_at') border-error @enderror" id="issued_at" name="issued_at" type="date" value="{{ old('issued_at', $invoice->issued_at?->format('Y-m-d')) }}" />
            @error('issued_at') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block font-label-md text-on-surface-variant mb-2" for="due_at">{{ __('Due Date') }}</label>
            <input class="{{ $inputClass }} @error('due_at') border-error @enderror" id="due_at" name="due_at" type="date" value="{{ old('due_at', $invoice->due_at?->format('Y-m-d')) }}" />
            @error('due_at') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="tax">{{ __('Tax') }}</label>
        <input class="{{ $inputClass }} @error('tax') border-error @enderror" id="tax" name="tax" type="number" step="0.01" min="0" value="{{ old('tax', $invoice->tax ?? 0) }}" />
        @error('tax') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block font-label-md text-on-surface-variant mb-2" for="discount">{{ __('Discount') }}</label>
        <input class="{{ $inputClass }} @error('discount') border-error @enderror" id="discount" name="discount" type="number" step="0.01" min="0" value="{{ old('discount', $invoice->discount ?? 0) }}" />
        @error('discount') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block font-label-md text-on-surface-variant mb-2" for="notes">{{ __('Notes') }}</label>
        <textarea class="{{ $inputClass }} @error('notes') border-error @enderror" id="notes" name="notes" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
        @error('notes') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-8 border-t border-slate-100 pt-8">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="font-h3 text-on-surface">{{ __('Line Items') }}</h3>
            <p class="text-body-sm text-on-surface-variant mt-1">{{ __('Add procedures or services to bill.') }}</p>
        </div>
        <button type="button" id="add-invoice-item" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline">
            <span class="material-symbols-outlined text-sm">add</span>
            {{ __('Add Line Item') }}
        </button>
    </div>

    @error('items') <p class="mb-4 text-body-sm text-error">{{ $message }}</p> @enderror

    <div id="invoice-items" class="space-y-3" data-services='@json($serviceOptions)'>
        @foreach ($items as $index => $item)
            <div class="invoice-item-row grid grid-cols-1 md:grid-cols-12 gap-3 items-end border border-slate-200 rounded-lg p-4 bg-slate-50/50">
                <div class="md:col-span-5">
                    <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Description') }} *</label>
                    <input class="{{ $inputClass }} item-description" name="items[{{ $index }}][description]" type="text" value="{{ $item['description'] ?? '' }}" required />
                </div>
                <div class="md:col-span-3">
                    <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Quick Add Service') }}</label>
                    <select class="{{ $inputClass }} service-picker">
                        <option value="">{{ __('Select service') }}</option>
                        @foreach ($clinicServices as $service)
                            <option value="{{ $service->id }}" data-name="{{ $service->name }}" data-price="{{ $service->price }}">{{ $service->code }} — {{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Qty') }}</label>
                    <input class="{{ $inputClass }} item-quantity" name="items[{{ $index }}][quantity]" type="number" min="1" value="{{ $item['quantity'] ?? 1 }}" required />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-label-sm text-on-surface-variant mb-1">{{ __('Unit Price') }} *</label>
                    <input class="{{ $inputClass }} item-unit-price" name="items[{{ $index }}][unit_price]" type="number" step="0.01" min="0" value="{{ $item['unit_price'] ?? '' }}" required />
                </div>
                <div class="md:col-span-1 flex justify-end">
                    <button type="button" class="remove-invoice-item text-error text-sm hover:underline">{{ __('Remove') }}</button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('invoice-items');
    const addButton = document.getElementById('add-invoice-item');
    if (!container || !addButton) return;

    const bindRow = (row) => {
        const picker = row.querySelector('.service-picker');
        const description = row.querySelector('.item-description');
        const unitPrice = row.querySelector('.item-unit-price');
        const removeBtn = row.querySelector('.remove-invoice-item');

        picker?.addEventListener('change', () => {
            const option = picker.selectedOptions[0];
            if (!option || !option.value) return;
            description.value = option.dataset.name || '';
            unitPrice.value = option.dataset.price || '';
        });

        removeBtn?.addEventListener('click', () => {
            if (container.querySelectorAll('.invoice-item-row').length <= 1) return;
            row.remove();
            reindexRows();
        });
    };

    const reindexRows = () => {
        container.querySelectorAll('.invoice-item-row').forEach((row, index) => {
            row.querySelector('.item-description').name = `items[${index}][description]`;
            row.querySelector('.item-quantity').name = `items[${index}][quantity]`;
            row.querySelector('.item-unit-price').name = `items[${index}][unit_price]`;
        });
    };

    container.querySelectorAll('.invoice-item-row').forEach(bindRow);

    addButton.addEventListener('click', () => {
        const index = container.querySelectorAll('.invoice-item-row').length;
        const template = container.querySelector('.invoice-item-row').cloneNode(true);
        template.querySelector('.item-description').value = '';
        template.querySelector('.item-quantity').value = '1';
        template.querySelector('.item-unit-price').value = '';
        template.querySelector('.service-picker').value = '';
        template.querySelector('.item-description').name = `items[${index}][description]`;
        template.querySelector('.item-quantity').name = `items[${index}][quantity]`;
        template.querySelector('.item-unit-price').name = `items[${index}][unit_price]`;
        container.appendChild(template);
        bindRow(template);
    });
});
</script>
