<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Lang;

class CourseDestroyed extends BaseNotification implements ShouldQueue
{
    /**
     * Constructs this notification.
     *
     * @param  User  $user
     * @param  string  $courseTitle
     */
    public function __construct(private readonly User $user, private readonly string $courseTitle)
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
                    ->greeting(Lang::get('Dear responsible'))
                    ->subject(Lang::get('Course deleted'))
                    ->line(Lang::get(':fullname has deleted the course ":course".', [
                        'fullname' => $this->user->fullName(),
                        'course' => $this->courseTitle,
                    ]));
    }
}
