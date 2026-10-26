<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Support\TenantMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Payment extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_number',
        'amount',
        'payment_method',
        'payment_reference',
        'paid_at',
        'notes',
        'proof_path',
        'confirmed_at',
        'confirmed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'confirmed_by');
    }

    public function proofUrl(): ?string
    {
        return TenantMedia::urlIfExists($this->proof_path);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function hasProof(): bool
    {
        return filled($this->proof_path);
    }
}
