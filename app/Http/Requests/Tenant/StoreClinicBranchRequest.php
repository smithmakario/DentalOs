<?php

namespace App\Http\Requests\Tenant;

use App\Services\ClinicAdminAccess;
use App\Support\BranchIdentifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClinicBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(ClinicAdminAccess::class)->canManageClinic();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(BranchIdentifier::fillMissing([
            'name' => tenant()?->organization?->name,
            'branch_name' => $this->input('branch_name'),
            'branch_slug' => $this->input('branch_slug'),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $central = (string) config('tenancy.database.central_connection');

        return [
            'branch_name' => ['required', 'string', 'max:255'],
            'branch_slug' => [
                'required',
                'string',
                'max:63',
                'alpha_dash',
                Rule::unique($central.'.tenants', 'slug'),
                Rule::unique($central.'.tenants', 'id'),
            ],
        ];
    }
}
