<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Lang;

class CourseEdited extends BaseNotification implements ShouldQueue
{
    /**
     * Constructs this notification.
     *
     * @param  User  $user
     * @param  Course  $course
     */
    public function __construct(protected User $user, protected Course $course)
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
                    ->subject(Lang::get('Course changed'))
                    ->line(Lang::get(':fullname has changed the course ":course".', [
                        'fullname' => $this->user->fullName(),
                        'course' => $this->course->title,
                    ]))
                    ->action(
                        Lang::get('View course'),
                        url(route('course.show', [$this->course, $this->course->slug]))
                    );
    }
}
