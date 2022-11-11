<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\SessionGroup;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\AdminSubscribe;
use App\Notifications\OwnerSubscribe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexNotLoggedIn(): void
    {
        $response = $this->get(route('subscriptions'));
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testIndexNoSubscriptions(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('subscriptions'));
        $response->assertStatus(200);
        $response->assertSee(__('common.no_subscriptions_for_me'));
    }

    public function testIndexHasSubscriptions(): void
    {
        $creator = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $course = Course::factory()->has(SessionGroup::factory())->create(['owner_id' => $creator]);
        $course2 = Course::factory()->has(SessionGroup::factory())->create(['owner_id' => $creator, 'title' => 'other course']);
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        Subscription::create(['user_id' => $user->id, 'session_group_id' => $course->sessionGroups[0]->id]);
        Subscription::create(['user_id' => $user2->id, 'session_group_id' => $course2->sessionGroups[0]->id]);

        $response = $this->actingAs($user)->get(route('subscriptions'));
        $response->assertStatus(200);
        $response->assertDontSee(__('common.no_subscriptions_for_me'));
        $response->assertSee($course->title);
        $response->assertDontSee($course2->title);
    }

    public function testIndexAdminNotAuthorized(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('subscriptions.show', ['user' => 42]));
        $response->assertStatus(404);
    }

    public function testIndexAdmin(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $inspectedUser = User::factory()->create();
        $course = Course::factory()->has(SessionGroup::factory())->create(['owner_id' => $user->id]);

        $subscription = Subscription::create(['user_id' => $inspectedUser->id, 'session_group_id' => $course->sessionGroups[0]->id]);

        $response = $this->actingAs($user)->get(route('subscriptions.show', ['user' => $inspectedUser->id]));
        $response->assertStatus(200);
        $response->assertSee($course->title);

        $subscription->delete();

        $response = $this->actingAs($user)->get(route('subscriptions.show', ['user' => $inspectedUser->id]));
        $response->assertStatus(200);
        $response->assertSee($course->title);
    }

    public function testSubscribeFailsBecauseUserIsOwner(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $course = Course::factory()->has(SessionGroup::factory())->create(['owner_id' => $user->id, 'last_date' => Carbon::now()->addDays(10)]);

        $this->actingAs($user)
            ->post(route('course.subscribe', ['sessionGroup' => $course->sessionGroups[0]]))
            ->assertStatus(302);
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function testSubscribeFailsBecauseUserIsTooLate(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $actingUser = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $course = Course::factory()->has(SessionGroup::factory())->create(['owner_id' => $user->id, 'last_date' => Carbon::now()->subDay()]);

        $this->actingAs($actingUser)
            ->post(route('course.subscribe', ['sessionGroup' => $course->sessionGroups[0]]))
            ->assertStatus(302);
        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function testSubscribeFailsBecauseGroupIsFull(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $actingUser = User::factory()->create();
        $course = Course::factory()->has(SessionGroup::factory(['max_ppl' => 5]))->create(['owner_id' => $user->id, 'last_date' => Carbon::now()->addDay()]);

        Subscription::factory()->for($course->sessionGroups[0])->count(5)->create();
        $this->assertDatabaseCount('subscriptions', 5);

        $this->actingAs($actingUser)
            ->post(route('course.subscribe', ['sessionGroup' => $course->sessionGroups[0]]))
            ->assertStatus(302);
        $this->assertDatabaseCount('subscriptions', 5);
    }

    public function testSubscribeFailsBecauseAlreadySubscribed(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $actingUser = User::factory()->create();
        $course = Course::factory()->has(SessionGroup::factory(['max_ppl' => 5]))->create(['owner_id' => $user->id, 'last_date' => Carbon::now()->addDay()]);

        Subscription::create(['user_id' => $actingUser->id, 'session_group_id' => $course->sessionGroups[0]->id]);

        $this->actingAs($actingUser)
            ->post(route('course.subscribe', ['sessionGroup' => $course->sessionGroups[0]]))
            ->assertStatus(302);
        $this->assertDatabaseCount('subscriptions', 1);
    }

    public function testSubscribeSucceeds(): void
    {
        Notification::fake();

        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $actingUser = User::factory()->create(['function' => 'something']);
        $course = Course::factory()->has(SessionGroup::factory(['max_ppl' => 2]))->create(['owner_id' => $user->id, 'last_date' => Carbon::now()->addDay(), 'notify_me' => true]);

        $this->assertDatabaseCount('subscriptions', 0);
        $this->actingAs($actingUser)
            ->post(route('course.subscribe', ['sessionGroup' => $course->sessionGroups[0]]))
            ->assertStatus(302);
        $this->assertDatabaseHas('subscriptions', ['user_id' => $actingUser->id, 'session_group_id' => $course->sessionGroups[0]->id]);

        Notification::assertSentTo($user, OwnerSubscribe::class, function (OwnerSubscribe $notification, $channels) use ($user, $course, $actingUser) {
            $contents = $notification->toMail($user)->render();
            $this->assertStringContainsString($actingUser->fullName(), $contents);
            $this->assertStringContainsString($course->title, $contents);

            return true;
        });

        Notification::assertSentTo(new AnonymousNotifiable, AdminSubscribe::class, function (AdminSubscribe $notification, $channels) use ($user, $course, $actingUser) {
            $contents = $notification->toMail($user)->render();
            $this->assertStringContainsString($actingUser->fullName(), $contents);
            $this->assertStringContainsString($course->title, $contents);

            return true;
        });
    }

    public function testUnsubscribeFailsBecauseTooLate(): void
    {
        Notification::fake();

        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $actingUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $course = Course::factory()->has(SessionGroup::factory(['max_ppl' => 2]))->create(['owner_id' => $user->id, 'last_date' => Carbon::now()->subDay(), 'notify_me' => true]);

        $subscriptionActingUser = Subscription::create(['user_id' => $actingUser->id, 'session_group_id' => $course->sessionGroups[0]->id]);
        Subscription::create(['user_id' => $otherUser->id, 'session_group_id' => $course->sessionGroups[0]->id]);
        $this->assertDatabaseCount('subscriptions', 2);

        $this->actingAs($actingUser)
            ->post(route('course.unsubscribe', ['subscription' => $subscriptionActingUser]))
            ->assertSessionMissing('success')
            ->assertStatus(302);
        $this->assertDatabaseCount('subscriptions', 2);

        Notification::assertNothingSent();
    }

    public function testUnsubscribeFailsBecauseNotTheUsersSubscription(): void
    {
        Notification::fake();

        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $actingUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $course = Course::factory()->has(SessionGroup::factory(['max_ppl' => 2]))->create(['owner_id' => $user->id, 'last_date' => Carbon::now()->addDay(), 'notify_me' => true]);

        Subscription::create(['user_id' => $actingUser->id, 'session_group_id' => $course->sessionGroups[0]->id]);
        $subscriptionOtherUser = Subscription::create(['user_id' => $otherUser->id, 'session_group_id' => $course->sessionGroups[0]->id]);
        $this->assertDatabaseCount('subscriptions', 2);

        $this->actingAs($actingUser)
            ->post(route('course.unsubscribe', ['subscription' => $subscriptionOtherUser]))
            ->assertSessionMissing('success')
            ->assertStatus(302);
        $this->assertDatabaseCount('subscriptions', 2);

        Notification::assertNothingSent();
    }

    public function testUnsubscribeSucceeds(): void
    {
        Notification::fake();

        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $actingUser = User::factory()->create(['member_nr' => '123456789']);
        $course = Course::factory()->has(SessionGroup::factory(['max_ppl' => 2]))->create(['owner_id' => $user->id, 'last_date' => Carbon::now()->addDay(), 'notify_me' => true]);

        $subscriptionData = ['user_id' => $actingUser->id, 'session_group_id' => $course->sessionGroups[0]->id];
        $subscription = Subscription::create($subscriptionData);

        $this->actingAs($actingUser)
            ->post(route('course.unsubscribe', ['subscription' => $subscription]))
            ->assertSessionHas('success')
            ->assertStatus(302);
        $this->assertDatabaseMissing('subscriptions', $subscriptionData);

        Notification::assertSentTo($user, OwnerSubscribe::class, function (OwnerSubscribe $notification, $channels) use ($user, $course, $actingUser) {
            $contents = $notification->toMail($user)->render();
            $this->assertStringContainsString($actingUser->fullName(), $contents);
            $this->assertStringContainsString($course->title, $contents);
            $this->assertStringContainsString('123456789', $contents);

            return true;
        });
    }

    public function testUnsubscribeResubscribe(): void
    {
        Notification::fake();

        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $actingUser = User::factory()->create();
        $course = Course::factory()->has(SessionGroup::factory(['max_ppl' => 2]))->create(['owner_id' => $user->id, 'last_date' => Carbon::now()->addDay(), 'notify_me' => true]);

        $subscriptionData = ['user_id' => $actingUser->id, 'session_group_id' => $course->sessionGroups[0]->id];
        $subscription = Subscription::create($subscriptionData);

        $this->actingAs($actingUser)
            ->post(route('course.unsubscribe', ['subscription' => $subscription]))
            ->assertSessionHas('success')
            ->assertStatus(302);
        $this->assertDatabaseMissing('subscriptions', $subscriptionData);

        Notification::assertSentTo($user, OwnerSubscribe::class);

        $this->actingAs($actingUser)
            ->post(route('course.subscribe', ['sessionGroup' => $course->sessionGroups[0]->id]))
            ->assertStatus(302);
        $this->assertDatabaseHas('subscriptions', $subscriptionData);

        Notification::assertSentTo($user, OwnerSubscribe::class);
    }
}
