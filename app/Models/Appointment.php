<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Appointment extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'provider_id',
        'title',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
        'soap_subjective',
        'soap_objective',
        'soap_assessment',
        'soap_plan',
        'checked_in_at',
        'completed_at',
        'reminder_24h_sent_at',
        'reminder_1h_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
            'reminder_24h_sent_at' => 'datetime',
            'reminder_1h_sent_at' => 'datetime',
            'status' => AppointmentStatus::class,
        ];
    }

    public function hasSoapNotes(): bool
    {
        return filled($this->soap_subjective)
            || filled($this->soap_objective)
            || filled($this->soap_assessment)
            || filled($this->soap_plan);
    }

    /**
     * Day-of clinical statuses (check-in through completion) stay locked
     * until the appointment date unless staff marks the change as an emergency.
     */
    public function statusRequiresAppointmentDay(AppointmentStatus $status): bool
    {
        return in_array($status, [
            AppointmentStatus::CheckedIn,
            AppointmentStatus::InProgress,
            AppointmentStatus::Completed,
            AppointmentStatus::NoShow,
        ], true);
    }

    public function isOnOrAfterScheduledDay(?CarbonInterface $moment = null): bool
    {
        $moment ??= now();

        return $moment->copy()->startOfDay()->gte($this->scheduled_at->copy()->startOfDay());
    }

    public function canApplyStatus(AppointmentStatus $status, bool $isEmergency = false): bool
    {
        if ($this->status === $status) {
            return false;
        }

        if (! $this->statusRequiresAppointmentDay($status)) {
            return true;
        }

        return $isEmergency || $this->isOnOrAfterScheduledDay();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'provider_id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function endsAt(): CarbonInterface
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    public static function providerHasConflict(
        int $providerId,
        CarbonInterface $scheduledAt,
        int $durationMinutes,
        ?int $excludeId = null,
    ): bool {
        $endsAt = $scheduledAt->copy()->addMinutes($durationMinutes);

        $query = static::query()
            ->where('provider_id', $providerId)
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
            ])
            ->when($excludeId, fn (Builder $query) => $query->where('id', '!=', $excludeId))
            ->where('scheduled_at', '<', $endsAt);

        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $query->whereRaw("datetime(scheduled_at, '+' || duration_minutes || ' minutes') > ?", [$scheduledAt]);
        } else {
            $query->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$scheduledAt]);
        }

        return $query->exists();
    }

    /** @param Builder<Appointment> $query */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
                AppointmentStatus::Completed->value,
            ])
            ->where('scheduled_at', '>=', now());
    }
}
