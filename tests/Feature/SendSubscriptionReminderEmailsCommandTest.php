<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\SessionGroup;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionReminder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendSubscriptionReminderEmailsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testCommandSuccess(): void
    {
        \Notification::fake();

        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $userThatWantsReminders = User::factory()->create(['reminders' => true]);
        $userThatDoesNotWantReminders = User::factory()->create(['reminders' => false]);

        $sessionGroups = SessionGroup::factory()->count(2)->for(Course::factory()->create(['owner_id' => $owner]))->create();
        $course = $sessionGroups[0]->course;
        $course->last_date = Carbon::now()->subDays(2);
        $course->save();

        Subscription::create(['user_id' => $userThatWantsReminders->id, 'session_group_id' => $sessionGroups[0]->id]);
        Subscription::create(['user_id' => $userThatDoesNotWantReminders->id, 'session_group_id' => $sessionGroups[1]->id]);

        $this->artisan('crepes:send-reminders')->assertExitCode(0);

        \Notification::assertNothingSent();

        $course->last_date = Carbon::now()->subDay();
        $course->save();

        $this->artisan('crepes:send-reminders')->assertExitCode(0);

        \Notification::assertSentTo($userThatWantsReminders, SubscriptionReminder::class, function ($notification, $channels) use ($userThatWantsReminders, $course) {
            $contents = $notification->toMail($userThatWantsReminders)->render();
            $this->assertStringContainsString($userThatWantsReminders->fullName(), $contents);
            $this->assertStringContainsString(route('course.show', ['course' => $course->id, 'slug' => $course->slug]), $contents);

            return true;
        });
    }
}
