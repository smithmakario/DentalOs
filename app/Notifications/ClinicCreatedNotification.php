<?php

namespace App\Notifications;

use App\Models\Organization;
use App\Models\Tenant;
use App\Support\TenantPortal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicCreatedNotification extends Notification
{
    public function __construct(
        public Organization $organization,
        public Tenant $adminBranch,
        public ?string $plainPassword = null,
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
        $branchLoginUrl = TenantPortal::loginUrl($this->adminBranch);
        $clinicLoginUrl = url('/clinic/login');
        $dashboardUrl = TenantPortal::url($this->adminBranch, '/dashboard');

        $message = (new MailMessage)
            ->subject(__('Your :clinic clinic is ready on :app', [
                'clinic' => $this->organization->name,
                'app' => config('app.name', 'DentaFlow'),
            ]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your clinic :clinic has been created on DentalOs.', [
                'clinic' => $this->organization->name,
            ]))
            ->line(__('Sign-in email: :email', ['email' => $notifiable->email]));

        if (filled($this->plainPassword)) {
            $message->line(__('Temporary password: :password', ['password' => $this->plainPassword]));
            $message->line(__('Please change this password after your first sign-in.'));
        }

        return $message
            ->line(__('Clinic portal (all branches): :url', ['url' => $clinicLoginUrl]))
            ->line(__('Branch login for :branch: :url', [
                'branch' => $this->adminBranch->name,
                'url' => $branchLoginUrl,
            ]))
            ->line(__('After sign-in you will land on :url', [
                'url' => $dashboardUrl,
            ]))
            ->line(__('A subscription must be active before staff can operate the clinic.'))
            ->action(__('Open clinic login'), $clinicLoginUrl)
            ->line(__('If you did not expect this email, contact support.'));
    }
}
