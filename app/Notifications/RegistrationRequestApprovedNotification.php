<?php

namespace App\Notifications;

use App\Models\OrganizationRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationRequestApprovedNotification extends Notification implements ShouldQueue
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
            ->subject(__('Your DentalOs Registration Has Been Approved'))
            ->greeting(__('Congratulations, :name!', ['name' => $request->contact_person]))
            ->line(__('Your registration request for :organization has been approved.', ['organization' => $request->name]))
            ->line(__('You can now complete your clinic setup and choose a subscription plan.'))
            ->action(__('Complete Setup'), route('onboarding.show', $request->onboarding_token))
            ->line(__('This link is unique to your application. Please do not share it with others.'));
    }
}
