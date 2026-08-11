<?php

namespace App\Http\Requests\Central;

use App\Enums\PlatformRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformPaymentSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->platform_role === PlatformRole::SuperAdmin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'bank_code' => ['nullable', 'string', 'max:20'],
            'currency' => ['required', 'string', 'size:3'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
