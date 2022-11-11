<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Lang;

class OwnerSubscribe extends BaseNotification implements ShouldQueue
{
    /**
     * @var array<string>
     */
    protected array $fields = ['member_nr', 'function'];

    public function __construct(protected User $user, protected Course $course, protected int $groupIndex, protected bool $unsub)
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
        if ($this->unsub) {
            $subject = 'Deregistration';
            $line = ':fullname has deregistered from the course ":course" from session group #:group.';
        } else {
            $subject = 'Registration';
            $line = ':fullname has registered for the course ":course" in session group #:group.';
        }

        $mail = parent::toMail($notifiable)
                    ->subject(Lang::get($subject))
                    ->line(Lang::get($line, [
                        'fullname' => $this->user->fullName(),
                        'course' => $this->course->title,
                        'group' => $this->groupIndex,
                    ]));

        foreach ($this->fields as $field) {
            $label = '**'.__('common.'.$field).'**: ';

            if (isset($this->user->{$field})) {
                $mail = $mail->line($label.Lang::get(':data', ['data' => $this->user->{$field}]));
            } else {
                $mail = $mail->line($label.'*'.__('common.not_provided').'*');
            }
        }

        return $mail->action(
            Lang::get('View course'),
            url(route('course.show', [$this->course, $this->course->slug]))
        );
    }
}
