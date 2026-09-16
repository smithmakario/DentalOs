<?php

namespace Database\Factories;

use App\Enums\InventoryTransactionType;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'type' => InventoryTransactionType::Restock,
            'quantity' => 10,
        ];
    }
}
