<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Notifications\SubscriptionReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendSubscriptionReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crepes:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends subscription reminder emails';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Note: Turns out "whereHas" creates a monster query...
        $courses = Course::with('sessionGroups.subscriptions.user')
                            ->whereDate('last_date', Carbon::yesterday())
                            ->get();

        foreach ($courses as $course) {
            foreach ($course->sessionGroups as $sg) {
                $mail = new SubscriptionReminder($sg);

                foreach ($sg->subscriptions as $subscription) {
                    $user = $subscription->user;
                    if ($user->reminders) {
                        $user->notify($mail);
                    }
                }
            }
        }
    }
}
