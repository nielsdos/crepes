<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Session;
use App\Models\SessionGroup;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseObserverTest extends TestCase
{
    use RefreshDatabase;

    public function testSubscriptionsAreSoftDeletedWhenCourseIsSoftDeleted(): void
    {
        $subscriber = User::factory()->create();
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $session = Session::factory()->for(SessionGroup::factory()->for(Course::factory()->state(['owner_id' => $owner->id])))->create();
        $sessionGroup = $session->sessionGroup;

        $subscription = Subscription::create(['user_id' => $subscriber->id, 'session_group_id' => $sessionGroup->id]);
        $this->assertFalse($subscription->trashed());

        $course = $sessionGroup->course;
        $course->delete();

        $subscription->refresh();
        $this->assertTrue($subscription->trashed());
    }
}
