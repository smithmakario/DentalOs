<?php

namespace App\Http\Requests\Central;

use App\Models\Organization;
use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateBranchPrimaryAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = $this->route('organization');
        $tenant = $this->route('tenant');

        return $this->user()?->isSuperAdmin() === true
            && $organization instanceof Organization
            && $tenant instanceof Tenant
            && (int) $tenant->organization_id === (int) $organization->id
            && $this->user()->can('manageBranches', $organization);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'admin_name' => __('admin name'),
            'admin_email' => __('admin email'),
            'admin_password' => __('admin password'),
        ];
    }
}
