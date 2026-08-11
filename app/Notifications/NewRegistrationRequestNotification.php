<?php

namespace App\Notifications;

use App\Models\OrganizationRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationRequestNotification extends Notification implements ShouldQueue
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
            ->subject(__('New Registration Request: :name', ['name' => $request->name]))
            ->greeting(__('New clinic registration request'))
            ->line(__('A new organization has requested access to DentalOs.'))
            ->line(__('Organization: :name', ['name' => $request->name]))
            ->line(__('Location: :location', ['location' => $request->location]))
            ->line(__('Contact: :person (:email)', ['person' => $request->contact_person, 'email' => $request->email]))
            ->line(__('Government approval: :approval', ['approval' => $request->government_approval]))
            ->action(__('Review Request'), route('registration-requests.show', $request));
    }
}
