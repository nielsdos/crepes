<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginFailureWrongCredentials()
    {
        $user = User::factory()->create();
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email')
            ->assertStatus(302);
        $this->assertFalse(auth()->hasUser());
    }

    public function testLoginFailureAlreadyLoggedIn()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user)->post('/login', [
            'email' => $user2->email,
            'password' => 'secret',
        ])->assertStatus(302);
        $this->assertTrue(auth()->user()->is($user));
    }

    public function testLoginSuccess()
    {
        $user = User::factory()->create();
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret',
        ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $this->assertTrue(auth()->hasUser());
    }

    public function testLoginSuccessRestored()
    {
        \Notification::fake();

        $user = User::factory()->state(['deleted_at' => Carbon::now()])->create();
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret',
        ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $this->assertTrue(auth()->hasUser());
        $user->refresh();
        $this->assertFalse($user->trashed());

        \Notification::assertSentTo($user, VerifyEmail::class);
    }
}
