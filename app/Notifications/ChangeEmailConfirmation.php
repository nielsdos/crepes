<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Lang;

class ChangeEmailConfirmation extends BaseNotification
{
    public function __construct(private readonly string $url)
    {
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return parent::toMail($notifiable)
                ->subject(Lang::get('Change email address'))
                ->line(Lang::get('You have requested to change the email address of your account. Please click the link below to verify your new email address.'))
                ->action(
                    Lang::get('Verify Email Address'),
                    $this->url
                );
    }
}
