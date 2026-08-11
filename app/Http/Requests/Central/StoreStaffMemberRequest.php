<?php

namespace App\Http\Requests\Central;

use App\Enums\StaffRole;
use App\Models\Organization;
use App\Models\StaffMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStaffMemberRequest extends FormRequest
{
    use ValidatesStaffBranchAccess;

    public function authorize(): bool
    {
        $organization = $this->route('organization');

        return $organization instanceof Organization
            && $this->user()->can('create', [StaffMember::class, $organization]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Organization $organization */
        $organization = $this->route('organization');

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('staff_members', 'email')->where('organization_id', $organization->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', Rule::enum(StaffRole::class)],
            'specialization' => ['nullable', 'string', 'max:150'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'has_global_branch_access' => ['required', 'boolean'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => [
                'string',
                Rule::exists('tenants', 'id')->where('organization_id', $organization->id),
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
