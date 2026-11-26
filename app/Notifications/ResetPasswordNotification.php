<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $expire = (int) config(
            'auth.passwords.'.config('auth.defaults.passwords').'.expire',
            60
        );

        return (new MailMessage)
            ->subject(__('notifications.password_reset.subject'))
            ->greeting(__('notifications.password_reset.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.password_reset.intro'))
            ->action(__('notifications.password_reset.action'), $this->resetUrl($notifiable))
            ->line(__('notifications.password_reset.expiry', ['count' => $expire]))
            ->line(__('notifications.password_reset.ignore'))
            ->salutation(__('notifications.password_reset.salutation'));
    }
}
