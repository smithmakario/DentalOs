<?php

namespace App\Notifications;

use App\Models\OrganizationRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationRequestRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public OrganizationRegistrationRequest $registrationRequest,
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
        $request = $this->registrationRequest;

        return (new MailMessage)
            ->subject(__('Update on Your DentalOs Registration Request'))
            ->greeting(__('Hello :name,', ['name' => $request->contact_person]))
            ->line(__('Thank you for your interest in DentalOs.'))
            ->line(__('After reviewing your registration request for :organization, we are unable to approve it at this time.', ['organization' => $request->name]))
            ->line(__('Reason: :reason', ['reason' => $request->rejection_reason]))
            ->line(__('If you believe this was made in error or would like to provide additional information, please contact our support team.'));
    }
}
