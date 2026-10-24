<?php

namespace App\Http\Requests\Central;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignAdminBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = $this->route('organization');

        return $this->user()?->isSuperAdmin() === true
            && $organization instanceof Organization
            && $this->user()->can('update', $organization);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Organization $organization */
        $organization = $this->route('organization');

        return [
            'admin_branch_id' => [
                'required',
                'string',
                Rule::exists('tenants', 'id')->where(
                    fn ($query) => $query->where('organization_id', $organization->id),
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'admin_branch_id' => __('admin branch'),
        ];
    }
}
