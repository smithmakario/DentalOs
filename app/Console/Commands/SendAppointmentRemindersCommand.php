<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Tenant;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendAppointmentRemindersCommand extends Command
{
    protected $signature = 'appointments:send-reminders {--tenant= : Optional tenant id}';

    protected $description = 'Send T-24h and T-1h appointment reminder emails to patients';

    public function handle(): int
    {
        $tenantQuery = Tenant::query()->where('is_active', true);

        if ($this->option('tenant')) {
            $tenantQuery->whereKey($this->option('tenant'));
        }

        $sent = 0;

        $tenantQuery->each(function (Tenant $tenant) use (&$sent): void {
            $tenant->run(function () use (&$sent): void {
                $sent += $this->sendForWindow('24h', now()->addHours(23), now()->addHours(25), 'reminder_24h_sent_at');
                $sent += $this->sendForWindow('1h', now()->addMinutes(45), now()->addMinutes(75), 'reminder_1h_sent_at');
            });
        });

        $this->info("Sent {$sent} appointment reminder(s).");

        return self::SUCCESS;
    }

    private function sendForWindow(string $window, mixed $from, mixed $to, string $column): int
    {
        $appointments = Appointment::query()
            ->with(['patient', 'provider'])
            ->whereNull($column)
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
                AppointmentStatus::Completed->value,
            ])
            ->whereBetween('scheduled_at', [$from, $to])
            ->whereHas('patient', fn ($query) => $query->whereNotNull('email')->where('email', '!=', ''))
            ->get();

        $count = 0;

        foreach ($appointments as $appointment) {
            Notification::route('mail', $appointment->patient->email)
                ->notify(new AppointmentReminderNotification($appointment, $window));

            $appointment->forceFill([$column => now()])->save();
            $count++;
        }

        return $count;
    }
}
