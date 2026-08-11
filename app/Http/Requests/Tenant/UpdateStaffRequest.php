<?php

namespace App\Http\Requests\Tenant;

use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('staff');

        return $member instanceof Staff
            && $this->user('staff')?->can('update', $member);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Staff $member */
        $member = $this->route('staff');

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', Password::defaults()],
            'role' => [
                'required',
                Rule::in(array_map(
                    fn (StaffRole $role): string => $role->value,
                    StaffRole::branchAssignable(),
                )),
            ],
            'specialization' => ['nullable', 'string', 'max:150'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
