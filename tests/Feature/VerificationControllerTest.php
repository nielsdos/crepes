<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ChangeEmailOld;
use App\Notifications\VerifyEmail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testFailureNotLoggedIn(): void
    {
        $user = User::factory()->create();
        $url = URL::temporarySignedRoute(
            'verification.change.email',
            now()->addMinutes(60), [
                'id' => $user->id,
                'p' => \Crypt::encryptString('new@email.com'),
            ]
        );
        $this->get($url)->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['id' => $user->id, 'email' => 'new@email.com']);
    }

    public function testFailureWrongId(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $url = URL::temporarySignedRoute(
            'verification.change.email',
            now()->addMinutes(60), [
                'id' => $user2->id,
                'p' => \Crypt::encryptString('new@email.com'),
            ]
        );
        $this->actingAs($user)->get($url)->assertStatus(302);
        $this->assertDatabaseMissing('users', ['id' => $user->id, 'email' => 'new@email.com']);
        $this->assertDatabaseMissing('users', ['id' => $user2->id, 'email' => 'new@email.com']);
    }

    public function testFailureWrongType(): void
    {
        $user = User::factory()->create();
        $url = URL::temporarySignedRoute(
            'verification.change.email',
            now()->addMinutes(60), [
                'id' => $user->id,
                'p' => null,
            ]
        );
        $this->actingAs($user)->get($url)->assertStatus(302);
        $this->assertDatabaseMissing('users', ['id' => $user->id, 'email' => 'new@email.com']);
    }

    public function testFailureDecryptError(): void
    {
        $user = User::factory()->create();
        $url = URL::temporarySignedRoute(
            'verification.change.email',
            now()->addMinutes(60), [
                'id' => $user->id,
                'p' => '123',
            ]
        );
        $this->actingAs($user)->get($url)->assertStatus(302);
        $this->assertDatabaseMissing('users', ['id' => $user->id, 'email' => 'new@email.com']);
    }

    public function testFailureEmailSniped(): void
    {
        $user = User::factory()->create();
        $url = URL::temporarySignedRoute(
            'verification.change.email',
            now()->addMinutes(60), [
                'id' => $user->id,
                'p' => \Crypt::encryptString('new@email.com'),
            ]
        );
        User::factory()->create(['email' => 'new@email.com']);
        $this->actingAs($user)->get($url)->assertSessionHas('fail')->assertStatus(302);
        $this->assertDatabaseMissing('users', ['id' => $user->id, 'email' => 'new@email.com']);
    }

    public function testSuccess(): void
    {
        \Notification::fake();

        $user = User::factory()->create();
        $url = URL::temporarySignedRoute(
            'verification.change.email',
            now()->addMinutes(60), [
                'id' => $user->id,
                'p' => \Crypt::encryptString('new@email.com'),
            ]
        );
        $this->actingAs($user)
            ->get($url)
            ->assertStatus(302);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'new@email.com']);

        \Notification::assertSentTo(new AnonymousNotifiable, ChangeEmailOld::class, function ($notification, $channels) use ($user) {
            $contents = $notification->toMail($user)->render();
            $this->assertStringContainsString($user->fullName(), $contents);
            $this->assertStringContainsString('new@email.com', $contents);

            return true;
        });
    }

    public function testRestoreAccountFlow(): void
    {
        \Notification::fake();

        $user = User::factory()->create(['deleted_at' => Carbon::now(), 'email_verified_at' => null]);
        $this->post(route('login'), ['email' => $user->email, 'password' => 'secret'])
            ->assertRedirect(route('verification.notice'));

        \Notification::assertSentTo($user, VerifyEmail::class, function ($notification, $channels) use ($user) {
            $url = $notification->toMail($user)->toArray()['actionUrl'];

            $this->get($url)->assertSessionHas('success', __('auth.restored_done'))->assertRedirect('/');

            $user->refresh();
            $this->assertNotNull($user->email_verified_at);
            $this->assertNull($user->deleted_at);

            return true;
        });
    }
}
