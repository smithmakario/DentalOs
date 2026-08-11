<?php

namespace App\Http\Requests\Tenant;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\ClinicService;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('staff')?->can('create', Appointment::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('scheduled_date') && $this->filled('scheduled_time')) {
            $this->merge([
                'scheduled_at' => $this->string('scheduled_date')->toString().' '.$this->string('scheduled_time')->toString().':00',
            ]);
        }

        if ($this->filled('service_id')) {
            $service = ClinicService::query()->find($this->integer('service_id'));

            if ($service !== null) {
                $this->merge([
                    'title' => $service->name,
                    'duration_minutes' => $service->duration_minutes,
                ]);
            }
        }

        $this->merge([
            'status' => AppointmentStatus::Scheduled->value,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', Rule::exists(Patient::class, 'id')],
            'provider_id' => ['required', Rule::exists(Staff::class, 'id')],
            'service_id' => ['required', Rule::exists(ClinicService::class, 'id')],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'title' => ['nullable', 'string', 'max:150'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'insurance_confirmed' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $scheduledAt = $this->date('scheduled_at');
            $providerId = (int) $this->input('provider_id');
            $duration = (int) $this->input('duration_minutes');

            if ($scheduledAt && Appointment::providerHasConflict($providerId, $scheduledAt, $duration)) {
                $validator->errors()->add('scheduled_time', __('This provider already has an appointment during that time.'));
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw (new ValidationException($validator))
            ->redirectTo(route('tenant.appointments.create', array_filter([
                'step' => 4,
                'patient_id' => $this->integer('patient_id') ?: null,
                'service_id' => $this->integer('service_id') ?: null,
                'provider_id' => $this->integer('provider_id') ?: null,
                'date' => $this->string('scheduled_date')->toString() ?: null,
                'time' => $this->string('scheduled_time')->toString() ?: null,
            ])));
    }
}
