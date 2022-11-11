<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Lang;

class PasswordChanged extends BaseNotification implements ShouldQueue
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return parent::toMail($notifiable)
                ->subject(Lang::get('Your password has changed'))
                ->line(Lang::get('We sent you this email to notify you that your password has changed.'))
                ->line(Lang::get('If you have changed your password yourself, then you can ignore this email.'))
                ->line(Lang::get('If you did not change your password, then your account is probably hacked. You can contact us by replying to this email.'));
    }
}
