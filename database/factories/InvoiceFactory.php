<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'invoice_number' => 'INV-'.now()->format('Y').'-'.fake()->unique()->numerify('####'),
            'subtotal' => 100,
            'tax' => 0,
            'discount' => 0,
            'total' => 100,
            'amount_paid' => 0,
            'status' => InvoiceStatus::Draft,
            'issued_at' => today(),
            'due_at' => today()->addDays(14),
        ];
    }
}
