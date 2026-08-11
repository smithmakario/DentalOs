<?php

namespace App\Models;

use App\Enums\RegistrationRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class OrganizationRegistrationRequest extends Model
{
    /** @use HasFactory<\Database\Factories\OrganizationRegistrationRequestFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'location',
        'government_approval',
        'contact_person',
        'email',
        'phone',
        'status',
        'rejection_reason',
        'onboarding_token',
        'reviewed_by',
        'reviewed_at',
        'organization_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isPending(): bool
    {
        return $this->status === RegistrationRequestStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === RegistrationRequestStatus::Approved;
    }

    public function canOnboard(): bool
    {
        return $this->status === RegistrationRequestStatus::Approved
            && filled($this->onboarding_token)
            && $this->organization_id === null;
    }

    /**
     * @return string
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
