<?php

namespace Tests\Unit\Export;

use App\Models\Course;
use App\Models\SessionGroup;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Exports\SubscribersExportable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SubscribersExportTest extends TestCase
{
    use RefreshDatabase;

    private Collection $users;

    private Course $course;

    private Course $other_course;

    protected function afterRefreshingDatabase()
    {
        $this->users = User::factory()->count(10)->create();
        $owner = User::factory()->create();

        $this->course = Course::factory()->create(['owner_id' => $owner->id]);
        $this->other_course = Course::factory()->create(['owner_id' => $owner->id]);

        $sessionGroup = SessionGroup::factory()->for($this->course)->create();
        foreach ($this->users as $user) {
            Subscription::factory()
                ->for($user)
                ->for($sessionGroup)
                ->create();
        }

        $this->course = Course::find($this->course->id);
    }

    public function testHeading(): void
    {
        $exportable = new SubscribersExportable($this->course, true);
        $this->assertCount(7, $exportable->heading());
        $exportable = new SubscribersExportable($this->course, false);
        $this->assertCount(6, $exportable->heading());
    }

    public function testEverySubscriberIsInTheCollection(): void
    {
        $exportable = new SubscribersExportable($this->course, true);
        $collection = $exportable->collection();
        $this->assertCount(count($this->users), $collection);

        $subscriptions = $this->course->subscriptions;
        foreach ($subscriptions as $subscription) {
            $this->assertTrue($this->users->where('id', '=', $subscription->user_id)->isNotEmpty());
        }
    }

    public function testNoSubscribers(): void
    {
        $exportable = new SubscribersExportable($this->other_course, true);
        $collection = $exportable->collection();
        $this->assertCount(0, $collection);
    }

    public function testMapping(): void
    {
        $exportable = new SubscribersExportable($this->course, true);
        $collection = $exportable->collection();
        foreach ($collection as $user) {
            $mapped = $exportable->map($user);
            $this->assertCount(count($exportable->heading()), $mapped);
        }
    }
}
