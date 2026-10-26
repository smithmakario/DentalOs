<?php

namespace App\Notifications;

use App\Models\Organization;
use App\Models\Tenant;
use App\Support\TenantPortal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicPrimaryAdminUpdatedNotification extends Notification
{
    public function __construct(
        public Organization $organization,
        public ?Tenant $adminBranch = null,
        public ?string $plainPassword = null,
        public ?string $previousEmail = null,
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
        $clinicLoginUrl = url('/clinic/login');

        $message = (new MailMessage)
            ->subject(__('Your :clinic admin sign-in details were updated', [
                'clinic' => $this->organization->name,
            ]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your primary admin account for :clinic on DentalOs was updated.', [
                'clinic' => $this->organization->name,
            ]));

        if (filled($this->previousEmail) && strcasecmp($this->previousEmail, $notifiable->email) !== 0) {
            $message->line(__('Previous email: :email', ['email' => $this->previousEmail]));
        }

        $message->line(__('Sign-in email: :email', ['email' => $notifiable->email]));

        if (filled($this->plainPassword)) {
            $message->line(__('Temporary password: :password', ['password' => $this->plainPassword]));
            $message->line(__('Please change this password after your first sign-in.'));
        } elseif (filled($this->previousEmail) && strcasecmp($this->previousEmail, $notifiable->email) !== 0) {
            $message->line(__('Your existing password is unchanged. Use forgot password on the login page if you need a reset.'));
        }

        $message->line(__('Clinic portal: :url', ['url' => $clinicLoginUrl]));

        if ($this->adminBranch instanceof Tenant) {
            $branchLoginUrl = TenantPortal::loginUrl($this->adminBranch);

            $message->line(__('Branch login for :branch: :url', [
                'branch' => $this->adminBranch->name,
                'url' => $branchLoginUrl,
            ]));
        }

        return $message
            ->action(__('Open clinic login'), $clinicLoginUrl)
            ->line(__('If you did not expect this email, contact support.'));
    }
}
