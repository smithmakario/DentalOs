<?php

namespace App\Http\Requests\Tenant;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');

        return $appointment instanceof Appointment
            && $this->user('staff')?->can('updateStatus', $appointment);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'is_emergency' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Appointment|null $appointment */
            $appointment = $this->route('appointment');
            $statusValue = $this->string('status')->toString();

            if (! $appointment instanceof Appointment || $statusValue === '') {
                return;
            }

            $status = AppointmentStatus::tryFrom($statusValue);

            if ($status === null) {
                return;
            }

            $isEmergency = $this->boolean('is_emergency');

            if (! $appointment->canApplyStatus($status, $isEmergency)) {
                $validator->errors()->add(
                    'status',
                    __('This status can only be set on the appointment day, unless you mark it as an emergency.'),
                );
            }
        });
    }
}
