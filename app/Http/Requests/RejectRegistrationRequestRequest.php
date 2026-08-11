<?php

namespace App\Http\Requests;

use App\Models\OrganizationRegistrationRequest;
use Illuminate\Foundation\Http\FormRequest;

class RejectRegistrationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('review', $this->route('registrationRequest')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'rejection_reason' => __('rejection reason'),
        ];
    }
}
