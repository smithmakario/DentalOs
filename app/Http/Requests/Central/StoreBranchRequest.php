<?php

namespace App\Http\Requests\Central;

use App\Support\TenantDomain;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('domain')) {
            $this->merge([
                'domain' => TenantDomain::normalize($this->input('domain')),
            ]);
        }
    }

    public function authorize(): bool
    {
        $organization = $this->route('organization');

        return $organization !== null && $this->user()->can('manageBranches', $organization);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'branch_name' => ['required', 'string', 'max:255'],
            'branch_slug' => ['required', 'string', 'max:63', 'alpha_dash', 'unique:tenants,slug'],
            'domain' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/', 'unique:domains,domain'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'branch_name' => __('branch name'),
            'branch_slug' => __('branch slug'),
        ];
    }
}
