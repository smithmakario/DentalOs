<?php

namespace App\Models;

use App\Enums\PlatformPaymentMethod;
use App\Enums\SubscriptionPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'organization_subscription_id',
        'organization_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'paystack_reference',
        'paystack_access_code',
        'manual_payment_reference',
        'manual_notes',
        'verified_by',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_method' => PlatformPaymentMethod::class,
            'status' => SubscriptionPaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(OrganizationSubscription::class, 'organization_subscription_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public static function generatePaystackReference(): string
    {
        return 'DF-'.strtoupper(Str::random(16));
    }
}
