<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lot_number' => ['nullable', 'string', 'max:255'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'initial_quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
