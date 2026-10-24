<?php

namespace App\Notifications;

use App\Models\Organization;
use App\Models\Tenant;
use App\Support\TenantPortal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public Tenant $adminBranch,
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
        $loginUrl = TenantPortal::loginUrl($this->adminBranch);
        $dashboardUrl = TenantPortal::url($this->adminBranch, '/dashboard');

        return (new MailMessage)
            ->subject(__('Your :clinic clinic is ready on :app', [
                'clinic' => $this->organization->name,
                'app' => config('app.name', 'DentaFlow'),
            ]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your clinic :clinic has been created successfully.', [
                'clinic' => $this->organization->name,
            ]))
            ->line(__('Share this login link with yourself and your staff for :branch:', [
                'branch' => $this->adminBranch->name,
            ]))
            ->line($loginUrl)
            ->line(__('After sign-in you will land on :url', [
                'url' => $dashboardUrl,
            ]))
            ->line(__('Sign in once a subscription is active to manage every branch and run HQ operations.'))
            ->action(__('Open clinic login'), $loginUrl)
            ->line(__('Keep your password secure. If you did not expect this email, contact support.'));
    }
}
