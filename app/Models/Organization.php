<?php

namespace App\Models;

use App\Enums\OrganizationSubscriptionStatus;
use App\Enums\OrganizationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Organization extends Model
{
    use CentralConnection;
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'email',
        'phone',
        'address',
        'settings',
        'is_active',
        'admin_branch_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function adminBranch(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'admin_branch_id');
    }

    public function isAdminBranch(Tenant|string|null $tenant): bool
    {
        if ($this->admin_branch_id === null || $tenant === null) {
            return false;
        }

        $tenantId = $tenant instanceof Tenant ? (string) $tenant->getTenantKey() : (string) $tenant;

        return (string) $this->admin_branch_id === $tenantId;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps()
            ->using(OrganizationUser::class);
    }

    public function directoryEntries(): HasMany
    {
        return $this->hasMany(BranchDirectoryEntry::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(OrganizationSubscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(OrganizationSubscription::class)
            ->where('status', OrganizationSubscriptionStatus::Active)
            ->latestOfMany();
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function staffMembers(): HasMany
    {
        return $this->hasMany(StaffMember::class);
    }
}
