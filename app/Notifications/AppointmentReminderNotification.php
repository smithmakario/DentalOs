<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\BranchProfile;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification
{
    public function __construct(
        public Appointment $appointment,
        public string $window,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment->loadMissing(['patient', 'provider']);
        $branch = BranchProfile::query()->first();
        $branchName = $branch?->name ?? tenant('name') ?? config('app.name');
        $when = $appointment->scheduled_at->timezone(config('app.timezone'))->format('l, F j · g:i A');
        $doctor = $appointment->provider->full_name;
        $lead = $this->window === '1h'
            ? __('Your appointment is in about 1 hour.')
            : __('Your appointment is tomorrow.');

        $message = (new MailMessage)
            ->subject(__('Appointment reminder — :branch', ['branch' => $branchName]))
            ->greeting(__('Hello :name,', ['name' => $appointment->patient->first_name]))
            ->line($lead)
            ->line(__('When: :when', ['when' => $when]))
            ->line(__('Doctor: :doctor', ['doctor' => $doctor]))
            ->line(__('Branch: :branch', ['branch' => $branchName]));

        if (filled($branch?->phone)) {
            $message->line(__('Clinic phone: :phone', ['phone' => $branch->phone]));
        }

        if (filled($appointment->title)) {
            $message->line(__('Visit: :title', ['title' => $appointment->title]));
        }

        return $message
            ->line(__('Reply to this email or call the clinic if you need to cancel or reschedule.'))
            ->salutation(__('See you soon — :branch', ['branch' => $branchName]));
    }
}
