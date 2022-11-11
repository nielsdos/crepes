<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Lang;

class AdminSubscribe extends OwnerSubscribe
{
    /**
     * @var array<string>
     */
    protected array $fields = ['email', 'member_nr', 'function'];

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return parent::toMail($notifiable)->greeting(Lang::get('Dear responsible'));
    }
}
