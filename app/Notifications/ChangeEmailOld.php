<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Lang;

class ChangeEmailOld extends BaseNotification implements ShouldQueue
{
    public function __construct(private readonly string $fullName, private readonly string $newEmail)
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
                ->greeting(Lang::get('Dear').' '.$this->fullName)
                ->subject(Lang::get('Your email address has changed'))
                ->line(Lang::get('We sent you this email to notify you that your email address has been changed to :newEmail.', ['newEmail' => $this->newEmail]))
                ->line(Lang::get('If you have changed your email address yourself, then you can ignore this email.'))
                ->line(Lang::get('If you did not change your email address, then your account is probably hacked. You can contact us by replying to this email.'));
    }
}
