<?php

namespace App\Http\Requests\Tenant;

use App\Enums\PatientDocumentCategory;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StorePatientDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('staff')?->can('create', \App\Models\PatientDocument::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::enum(PatientDocumentCategory::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'recorded_at' => ['nullable', 'date', 'before_or_equal:today'],
            'file' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'webp', 'pdf', 'dcm'])
                    ->max(15 * 1024),
            ],
        ];
    }
}
