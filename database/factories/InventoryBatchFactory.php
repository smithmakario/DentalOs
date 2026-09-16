<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryBatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'lot_number' => $this->faker->bothify('LOT-####-???'),
            'initial_quantity' => 100,
            'current_quantity' => 100,
            'expiry_date' => now()->addYear(),
            'is_active' => true,
        ];
    }
}
