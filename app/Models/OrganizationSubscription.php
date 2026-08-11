<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\OrganizationSubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationSubscription extends Model
{
    protected $fillable = [
        'organization_id',
        'subscription_plan_id',
        'billing_cycle',
        'status',
        'current_period_start',
        'current_period_end',
    ];

    protected function casts(): array
    {
        return [
            'billing_cycle' => BillingCycle::class,
            'status' => OrganizationSubscriptionStatus::class,
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function amountForCycle(): string
    {
        return $this->billing_cycle === BillingCycle::Yearly
            ? (string) ($this->plan?->price_yearly ?? $this->plan?->price_monthly ?? 0)
            : (string) ($this->plan?->price_monthly ?? 0);
    }
}
