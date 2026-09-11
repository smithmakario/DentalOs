<?php

namespace App\Http\Requests\Tenant;

use App\Enums\InventoryItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_category_id' => ['required', 'exists:inventory_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::enum(InventoryItemType::class)],
            'unit_measure' => ['required', 'string', 'max:50'],
            'min_stock_level' => ['required', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
