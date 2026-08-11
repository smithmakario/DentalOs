<?php

namespace App\Http\Requests\Central;

use App\Enums\OrganizationType;
use App\Models\Organization;
use App\Support\TenantDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreClinicRequest extends FormRequest
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
        return $this->user()->can('create', Organization::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(OrganizationType::class)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'branch_name' => ['required', 'string', 'max:255'],
            'branch_slug' => ['required', 'string', 'max:63', 'alpha_dash', 'unique:tenants,slug'],
            'domain' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/', 'unique:domains,domain'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', Password::defaults()],
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
            'admin_name' => __('admin name'),
            'admin_email' => __('admin email'),
            'admin_password' => __('admin password'),
            'logo' => __('clinic logo'),
        ];
    }
}
