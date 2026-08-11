@php
    $styles = match ($status) {
        App\Enums\AppointmentStatus::Scheduled => 'bg-slate-100 text-slate-700 border-slate-200',
        App\Enums\AppointmentStatus::Confirmed => 'bg-blue-50 text-blue-700 border-blue-200',
        App\Enums\AppointmentStatus::CheckedIn => 'bg-teal-50 text-teal-700 border-teal-200',
        App\Enums\AppointmentStatus::InProgress => 'bg-orange-50 text-orange-700 border-orange-200',
        App\Enums\AppointmentStatus::Completed => 'bg-green-50 text-green-700 border-green-200',
        App\Enums\AppointmentStatus::Cancelled => 'bg-red-50 text-red-700 border-red-200',
        App\Enums\AppointmentStatus::NoShow => 'bg-slate-100 text-slate-500 border-slate-200',
    };
    $label = ucfirst(str_replace('_', ' ', $status->value));
@endphp

<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold border {{ $styles }}">
    {{ strtoupper($label) }}
</span>
