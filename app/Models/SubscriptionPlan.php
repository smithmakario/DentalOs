<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\OrganizationType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'organization_type',
        'description',
        'price_monthly',
        'price_yearly',
        'max_branches',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'organization_type' => OrganizationType::class,
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function organizationSubscriptions(): HasMany
    {
        return $this->hasMany(OrganizationSubscription::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function forOrganizationType(Builder $query, OrganizationType $type): void
    {
        $query->where('organization_type', $type);
    }

    public function priceFor(BillingCycle $cycle): string
    {
        if ($cycle === BillingCycle::Yearly && $this->price_yearly !== null) {
            return (string) $this->price_yearly;
        }

        return (string) $this->price_monthly;
    }
}
