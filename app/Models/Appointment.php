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

class Appointment extends Model
{
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
        'checked_in_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
            'status' => AppointmentStatus::class,
        ];
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
