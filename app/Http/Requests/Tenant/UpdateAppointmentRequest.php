<?php

namespace App\Http\Requests\Tenant;

use App\Models\Appointment;
use Illuminate\Validation\Validator;

class UpdateAppointmentRequest extends StoreAppointmentRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');

        return $appointment instanceof Appointment
            && $this->user('staff')?->can('update', $appointment);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['scheduled_at'] = ['required', 'date'];

        return $rules;
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
            $appointment = $this->route('appointment');

            if ($scheduledAt && $appointment instanceof Appointment && Appointment::providerHasConflict(
                $providerId,
                $scheduledAt,
                $duration,
                $appointment->id,
            )) {
                $validator->errors()->add('scheduled_at', __('This provider already has an appointment during that time.'));
            }
        });
    }
}
