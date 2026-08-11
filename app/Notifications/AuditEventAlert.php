<?php

namespace App\Notifications;

use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuditEventAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AuditLog $auditLog,
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
        $auditLog = $this->auditLog;
        $auditLog->loadMissing('organization');

        return (new MailMessage)
            ->subject(__('DentaFlow Audit Alert: :action', ['action' => $auditLog->action->label()]))
            ->greeting(__('Audit event recorded'))
            ->line($auditLog->description)
            ->line(__('Action: :action', ['action' => $auditLog->action->label()]))
            ->line(__('Performed by: :user', ['user' => $auditLog->user_name ?? __('Unknown user')]))
            ->when($auditLog->organization !== null, fn (MailMessage $message) => $message->line(__('Clinic: :clinic', ['clinic' => $auditLog->organization->name])))
            ->line(__('Time: :time', ['time' => $auditLog->created_at->toDayDateTimeString()]))
            ->action(__('View Audit Log'), route('audit-logs.index'));
    }
}
