<?php

namespace App\Notifications;

use App\Models\SessionGroup;

class SubscriptionReminder extends BaseNotification
{
    /**
     * Constructs the reminder mail.
     *
     * @param  SessionGroup  $sessionGroup
     */
    public function __construct(private SessionGroup $sessionGroup)
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
                ->subject('Registration reminder')
                ->markdown('mail.reminder.subscription', [
                    'url' => route('course.show', [$this->sessionGroup->course->id, $this->sessionGroup->course->slug]),
                    'course' => $this->sessionGroup->course,
                    'sessions' => $this->sessionGroup->sessions,
                ]);
    }
}
