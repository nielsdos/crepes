<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPassword;
use App\Notifications\ResetPasswordAccountNotFound;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Tests\TestCase;

class ForgotAndResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testResetLinkEmailPage(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function testSendResetLinkEmailFailCaptcha(): void
    {
        $this->post(route('password.email'), ['email' => 'does@not-exist.com'])
            ->assertSessionHasErrors('g-recaptcha-response')
            ->assertStatus(302);
    }

    public function testSendResetLinkEmailFailDoesntExist(): void
    {
        \Notification::fake();
        \NoCaptcha::shouldReceive('verifyResponse')->once()->andReturn(true);

        $this->post(route('password.email'), ['email' => 'does@not-exist.com', 'g-recaptcha-response' => 1])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        \Notification::assertSentTo(new AnonymousNotifiable, ResetPasswordAccountNotFound::class, function ($notification, $channels) {
            $notification->toMail(new AnonymousNotifiable)->render(); // Test render succeeds

            return true;
        });
    }

    public function testResetPasswordSuccessFlow(): void
    {
        \Notification::fake();
        \NoCaptcha::shouldReceive('verifyResponse')->once()->andReturn(true);

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email, 'g-recaptcha-response' => 1])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        \Notification::assertSentTo($user, ResetPassword::class, function ($notification, $channels) use ($user) {
            $url = $notification->toMail($user)->toArray()['actionUrl'];

            $response = $this->get($url)->assertOk();
            $matches = [];
            preg_match('/<input type="hidden" name="token" value="(.*)">/', $response->getContent(), $matches);
            $token = $matches[1];

            // Test case where the email is not verified. The reset should verify it.
            $user->email_verified_at = null;
            $user->save();

            $data = [
                'email' => $user->email,
                'token' => $token,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ];
            $this->post(route('password.update'), $data)->assertStatus(302);

            $user->refresh();
            $this->assertTrue(\Hash::check('secret123', $user->password));
            $this->assertNotNull($user->email_verified_at);

            return true;
        });
    }

    public function testResetPasswordSuccessFlowDeactivatedAccount(): void
    {
        \Notification::fake();
        \NoCaptcha::shouldReceive('verifyResponse')->once()->andReturn(true);

        $user = User::factory()->create(['deleted_at' => Carbon::now(), 'email_verified_at' => null]);

        $this->post(route('password.email'), ['email' => $user->email, 'g-recaptcha-response' => 1])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        \Notification::assertSentTo($user, ResetPassword::class, function ($notification, $channels) use ($user) {
            $url = $notification->toMail($user)->toArray()['actionUrl'];

            $response = $this->get($url)->assertOk();
            $matches = [];
            preg_match('/<input type="hidden" name="token" value="(.*)">/', $response->getContent(), $matches);
            $token = $matches[1];

            $data = [
                'email' => $user->email,
                'token' => $token,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ];
            $this->post(route('password.update'), $data)->assertSessionHas('success', __('auth.restored_done'))->assertStatus(302);

            $user->refresh();
            $this->assertTrue(\Hash::check('secret123', $user->password));
            $this->assertNotNull($user->email_verified_at);
            $this->assertNull($user->deleted_at);

            return true;
        });
    }
}
