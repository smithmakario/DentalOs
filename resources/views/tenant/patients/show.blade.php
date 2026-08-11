<x-tenant-layout>
    <div class="p-8 max-w-5xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="flex justify-between items-start mb-8">
            <div>
                <a class="inline-flex items-center gap-1 text-body-md text-primary hover:underline mb-4" href="{{ route('tenant.patients.index') }}">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    {{ __('Back to patients') }}
                </a>
                <h2 class="font-h1 text-h1 text-on-surface mb-2">{{ $patient->full_name }}</h2>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    {{ __('Patient since :date', ['date' => $patient->created_at->format('M j, Y')]) }}
                </p>
            </div>
            <div class="flex gap-3">
                @can('create', App\Models\Appointment::class)
                    <a class="flex items-center gap-2 border border-slate-200 text-on-surface px-6 py-2.5 rounded-lg font-label-md hover:bg-slate-50 transition-colors" href="{{ route('tenant.appointments.create', ['patient_id' => $patient->id]) }}">
                        <span class="material-symbols-outlined text-[18px]">event</span>
                        {{ __('Schedule Visit') }}
                    </a>
                @endcan
                @can('create', App\Models\TreatmentPlan::class)
                    <a class="flex items-center gap-2 border border-slate-200 text-on-surface px-6 py-2.5 rounded-lg font-label-md hover:bg-slate-50 transition-colors" href="{{ route('tenant.treatment-plans.create', ['patient_id' => $patient->id]) }}">
                        <span class="material-symbols-outlined text-[18px]">medical_services</span>
                        {{ __('New Treatment Plan') }}
                    </a>
                @endcan
                @can('create', App\Models\Invoice::class)
                    <a class="flex items-center gap-2 border border-slate-200 text-on-surface px-6 py-2.5 rounded-lg font-label-md hover:bg-slate-50 transition-colors" href="{{ route('tenant.invoices.create', ['patient_id' => $patient->id]) }}">
                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                        {{ __('New Invoice') }}
                    </a>
                @endcan
                @can('update', $patient)
                    <a class="flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90 transition-opacity" href="{{ route('tenant.patients.edit', $patient) }}">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        {{ __('Edit Patient') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ $patient->appointments_count }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Appointments') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ $patient->treatment_plans_count }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Treatment Plans') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ $patient->documents_count }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Documents') }}</div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="text-2xl font-bold text-on-surface">{{ $patient->invoices_count }}</div>
                <div class="text-on-surface-variant text-body-sm">{{ __('Invoices') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Personal Information') }}</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Date of Birth') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ $patient->date_of_birth?->format('F j, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Gender') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ $patient->gender ? ucfirst(str_replace('_', ' ', $patient->gender)) : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Address') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ $patient->address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Status') }}</dt>
                        <dd class="mt-1">
                            @if ($patient->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">{{ __('ACTIVE') }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">{{ __('INACTIVE') }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                <h3 class="font-h3 text-on-surface mb-4">{{ __('Contact & Payment') }}</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Phone') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ $patient->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Email') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ $patient->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Emergency Contact') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">
                            {{ $patient->emergency_contact_name ?? '—' }}
                            @if ($patient->emergency_contact_phone)
                                <span class="text-on-surface-variant"> · {{ $patient->emergency_contact_phone }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Preferred Payment') }}</dt>
                        <dd class="text-body-md text-on-surface mt-1">{{ $patient->preferred_payment_method?->label() ?? '—' }}</dd>
                    </div>
                    @if ($patient->usesHmo())
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('HMO Provider') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">{{ $patient->insurance_provider ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('HMO Plan') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">{{ $patient->hmo_plan ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Member ID') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">{{ $patient->insurance_number ?? '—' }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if ($patient->medical_notes)
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                    <h3 class="font-h3 text-on-surface mb-4">{{ __('Medical Notes') }}</h3>
                    <p class="text-body-md text-on-surface whitespace-pre-line">{{ $patient->medical_notes }}</p>
                </div>
            @endif

            @if ($patient->treatmentPlans->isNotEmpty())
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-h3 text-on-surface">{{ __('Treatment Plans') }}</h3>
                        <a class="text-primary font-label-md hover:underline" href="{{ route('tenant.treatment-plans.index', ['search' => $patient->last_name]) }}">{{ __('View All') }}</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($patient->treatmentPlans as $plan)
                            <a class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors" href="{{ route('tenant.treatment-plans.show', $plan) }}">
                                <div>
                                    <p class="font-body-md font-semibold text-on-surface">{{ $plan->title }}</p>
                                    <p class="text-body-sm text-outline">{{ $plan->provider->full_name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-label-md text-on-surface">{{ number_format($plan->estimated_total, 2) }}</p>
                                    <p class="text-body-sm text-outline">{{ ucfirst($plan->status->value) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-h3 text-on-surface">{{ __('Clinical Documents') }}</h3>
                    <p class="text-body-sm text-on-surface-variant mt-1">{{ __('Lab results, X-rays, imaging, and other patient files.') }}</p>
                </div>

                @can('create', App\Models\PatientDocument::class)
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <form method="POST" action="{{ route('tenant.patients.documents.store', $patient) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @csrf
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="document_category">{{ __('Category') }}</label>
                                <select class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md" id="document_category" name="category" required>
                                    @foreach (\App\Enums\PatientDocumentCategory::cases() as $category)
                                        <option value="{{ $category->value }}" @selected(old('category') === $category->value)>{{ $category->label() }}</option>
                                    @endforeach
                                </select>
                                @error('category') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="document_title">{{ __('Title') }}</label>
                                <input class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md @error('title') border-error @enderror" id="document_title" name="title" type="text" value="{{ old('title') }}" required />
                                @error('title') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="recorded_at">{{ __('Recorded Date') }}</label>
                                <input class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md" id="recorded_at" name="recorded_at" type="date" value="{{ old('recorded_at') }}" />
                                @error('recorded_at') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="document_file">{{ __('File') }}</label>
                                <input class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md @error('file') border-error @enderror" id="document_file" name="file" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,.dcm" required />
                                <p class="mt-1 text-body-sm text-outline">{{ __('Images, PDF, or DICOM up to 15 MB.') }}</p>
                                @error('file') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block font-label-md text-on-surface-variant mb-2" for="document_description">{{ __('Notes') }}</label>
                                <textarea class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-body-md" id="document_description" name="description" rows="2">{{ old('description') }}</textarea>
                                @error('description') <p class="mt-1 text-body-sm text-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <button class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md hover:opacity-90" type="submit">
                                    <span class="material-symbols-outlined text-[18px]">upload</span>
                                    {{ __('Upload Document') }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endcan

                @if ($patient->documents->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach ($patient->documents as $document)
                            <div class="flex items-center justify-between px-6 py-4 gap-4">
                                <div class="flex items-start gap-4 min-w-0">
                                    <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                                        <span class="material-symbols-outlined">
                                            @if ($document->category === \App\Enums\PatientDocumentCategory::Xray || $document->category === \App\Enums\PatientDocumentCategory::Imaging)
                                                radiology
                                            @elseif ($document->category === \App\Enums\PatientDocumentCategory::LabResult)
                                                science
                                            @else
                                                description
                                            @endif
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-body-md font-semibold text-on-surface truncate">{{ $document->title }}</p>
                                        <p class="text-body-sm text-outline">
                                            {{ $document->category->label() }}
                                            · {{ $document->formattedFileSize() }}
                                            @if ($document->recorded_at)
                                                · {{ $document->recorded_at->format('M j, Y') }}
                                            @endif
                                        </p>
                                        @if ($document->description)
                                            <p class="text-body-sm text-on-surface-variant mt-1">{{ $document->description }}</p>
                                        @endif
                                        <p class="text-xs text-slate-400 mt-1">{{ __('Uploaded by :name', ['name' => $document->uploader->full_name]) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a class="inline-flex items-center gap-1 text-primary font-label-md hover:underline" href="{{ route('tenant.patients.documents.download', [$patient, $document]) }}">
                                        <span class="material-symbols-outlined text-sm">download</span>
                                        {{ __('Download') }}
                                    </a>
                                    @can('delete', $document)
                                        <form method="POST" action="{{ route('tenant.patients.documents.destroy', [$patient, $document]) }}" onsubmit="return confirm(@js(__('Delete this document?')))">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-error font-label-md hover:underline" type="submit">{{ __('Delete') }}</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-12 text-center text-body-md text-on-surface-variant">
                        {{ __('No documents uploaded yet.') }}
                    </div>
                @endif
            </div>

            @if ($patient->invoices->isNotEmpty())
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-h3 text-on-surface">{{ __('Invoices') }}</h3>
                        @can('viewAny', App\Models\Invoice::class)
                            <a class="text-primary font-label-md hover:underline" href="{{ route('tenant.invoices.index', ['search' => $patient->last_name]) }}">{{ __('View All') }}</a>
                        @endcan
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($patient->invoices as $invoice)
                            <a class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors" href="{{ route('tenant.invoices.show', $invoice) }}">
                                <div>
                                    <p class="font-body-md font-semibold text-on-surface">{{ $invoice->invoice_number }}</p>
                                    <p class="text-body-sm text-outline">{{ $invoice->issued_at?->format('M j, Y') ?? __('Draft') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-label-md text-on-surface">{{ number_format($invoice->total, 2) }}</p>
                                    <p class="text-body-sm text-outline">{{ $invoice->status->label() }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($patient->appointments->isNotEmpty())
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-h3 text-on-surface">{{ __('Recent Appointments') }}</h3>
                        @can('viewAny', App\Models\Appointment::class)
                            <a class="text-primary font-label-md hover:underline" href="{{ route('tenant.appointments.index', ['search' => $patient->last_name]) }}">{{ __('View All') }}</a>
                        @endcan
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($patient->appointments as $appointment)
                            <a class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors" href="{{ route('tenant.appointments.show', $appointment) }}">
                                <div>
                                    <p class="font-body-md font-semibold text-on-surface">{{ $appointment->title ?: __('Visit') }}</p>
                                    <p class="text-body-sm text-outline">{{ $appointment->provider->full_name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-label-md text-on-surface">{{ $appointment->scheduled_at->format('M j, Y · g:i A') }}</p>
                                    <p class="text-body-sm text-outline">{{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-tenant-layout>
