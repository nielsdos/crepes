<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Lang;

trait AddGreetingTrait
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
                ->greeting(Lang::get('Dear').' '.$notifiable->fullName());
    }
}
