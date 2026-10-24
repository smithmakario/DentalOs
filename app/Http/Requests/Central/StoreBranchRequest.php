<?php

namespace App\Http\Requests\Central;

use App\Support\BranchIdentifier;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(BranchIdentifier::fillMissing([
            'branch_name' => $this->input('branch_name'),
            'branch_slug' => $this->input('branch_slug'),
        ]));
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
