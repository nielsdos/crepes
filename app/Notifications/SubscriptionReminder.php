<?php

namespace App\Notifications;

use App\Models\SessionGroup;
use Illuminate\Support\Facades\Lang;

class SubscriptionReminder extends BaseNotification
{
    public function __construct(private readonly SessionGroup $sessionGroup)
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
                ->subject(Lang::get('Registration reminder'))
                ->markdown('mail.reminder.subscription', [
                    'url' => route('course.show', [$this->sessionGroup->course->id, $this->sessionGroup->course->slug]),
                    'course' => $this->sessionGroup->course,
                    'sessions' => $this->sessionGroup->sessions,
                ]);
    }
}
