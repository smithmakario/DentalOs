<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class StaffResetPassword extends ResetPassword
{
    protected function resetUrl($notifiable): string
    {
        return url(route('tenant.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
