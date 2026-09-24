<?php

namespace App\Http\Requests\Tenant;

use App\Enums\InventoryItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inventory_category_id' => ['required', Rule::exists('inventory_categories', 'id')->where('tenant_id', tenant('id'))],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::enum(InventoryItemType::class)],
            'unit_measure' => ['required', 'string', 'max:50'],
            'min_stock_level' => ['required', 'numeric', 'min:0'],
            'current_stock_level' => ['required', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
