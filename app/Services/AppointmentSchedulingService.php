<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\StaffRole;
use App\Models\Appointment;
use App\Models\Staff;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AppointmentSchedulingService
{
    /** @var list<string> */
    private array $businessHours = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'];

    /**
     * @return Collection<int, Staff>
     */
    public function dentists(): Collection
    {
        return Staff::query()
            ->where('role', StaffRole::Dentist)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function nextAvailableSlot(int $providerId, int $durationMinutes): ?CarbonInterface
    {
        $start = now()->addHour()->startOfHour();

        for ($day = 0; $day < 21; $day++) {
            $date = $start->copy()->addDays($day)->startOfDay();

            if ($date->isSunday()) {
                continue;
            }

            foreach ($this->businessHours as $time) {
                $slot = $date->copy()->setTimeFromTimeString($time.':00');

                if ($slot->lte(now())) {
                    continue;
                }

                if (! $this->slotIsAvailable($providerId, $slot, $durationMinutes)) {
                    continue;
                }

                return $slot;
            }
        }

        return null;
    }

    /**
     * @return array{morning: list<array{time: string, label: string, available: bool}>, afternoon: list<array{time: string, label: string, available: bool}>, evening: list<array{time: string, label: string, available: bool}>}
     */
    public function slotsForDay(int $providerId, CarbonInterface $date, int $durationMinutes): array
    {
        $grouped = [
            'morning' => [],
            'afternoon' => [],
            'evening' => [],
        ];

        foreach ($this->businessHours as $time) {
            $slot = $date->copy()->setTimeFromTimeString($time.':00');
            $hour = (int) $slot->format('H');

            $period = match (true) {
                $hour < 12 => 'morning',
                $hour < 17 => 'afternoon',
                default => 'evening',
            };

            $available = $slot->gt(now()) && $this->slotIsAvailable($providerId, $slot, $durationMinutes);

            $grouped[$period][] = [
                'time' => $time,
                'label' => $slot->format('g:i A'),
                'available' => $available,
            ];
        }

        return $grouped;
    }

    /**
     * @return list<array{date: CarbonInterface, in_month: bool, is_today: bool, is_selected: bool, is_past: bool, has_availability: bool}>
     */
    public function calendarDays(CarbonInterface $month, int $providerId, int $durationMinutes, ?CarbonInterface $selectedDate = null): array
    {
        $start = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $days = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $hasAvailability = false;

            if ($day->isSameMonth($month) && ! ($day->isPast() && ! $day->isToday()) && ! $day->isSunday()) {
                $hasAvailability = collect($this->businessHours)->contains(
                    function (string $time) use ($providerId, $day, $durationMinutes): bool {
                        $slot = $day->copy()->setTimeFromTimeString($time.':00');

                        if ($slot->lte(now())) {
                            return false;
                        }

                        return $this->slotIsAvailable($providerId, $slot, $durationMinutes);
                    },
                );
            }

            $days[] = [
                'date' => $day->copy(),
                'in_month' => $day->isSameMonth($month),
                'is_today' => $day->isToday(),
                'is_selected' => $selectedDate !== null && $day->isSameDay($selectedDate),
                'is_past' => $day->isPast() && ! $day->isToday(),
                'has_availability' => $hasAvailability,
            ];
        }

        return $days;
    }

    public function slotIsAvailable(int $providerId, CarbonInterface $slot, int $durationMinutes): bool
    {
        if ($slot->isSunday()) {
            return false;
        }

        return ! Appointment::providerHasConflict(
            $providerId,
            $slot,
            $durationMinutes,
        );
    }

    public function parseMonth(string $month): CarbonInterface
    {
        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        }

        return now()->startOfMonth();
    }
}
