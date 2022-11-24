<?php

namespace Tests\Feature;

use Anhskohbo\NoCaptcha\Facades\NoCaptcha;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRegisterLoggedIn(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/register')
            ->assertRedirect('/');
    }

    /**
     * @dataProvider registerDataProvider
     */
    public function testRegisterFailureInputs(array $data, array $invalidFields): void
    {
        $this->post(route('register'), $data)
            ->assertSessionHasErrors($invalidFields)
            ->assertStatus(302);
    }

    public function testRegisterSuccess(): void
    {
        \Notification::fake();

        NoCaptcha::shouldReceive('verifyResponse')->once()->andReturn(true);

        $this->assertDatabaseCount('users', 0);
        $this->post(route('register'), [
            'firstname' => 'A',
            'lastname' => 'B',
            'email' => 'some_new_user@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'function' => 'some function',
            'member_nr' => '123456789',
            'g-recaptcha-response' => 1,
        ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $this->assertDatabaseHas('users', ['firstname' => 'A', 'lastname' => 'B', 'email' => 'some_new_user@example.com', 'function' => 'some function', 'member_nr' => '123456789', 'reminders' => false]);

        \Notification::assertSentTimes(VerifyEmail::class, 1);
    }

    public static function registerDataProvider(): array
    {
        return [
            [
                [],
                ['firstname', 'lastname', 'email', 'password', 'g-recaptcha-response'],
            ],
            [
                ['firstname' => 'A'],
                ['lastname', 'email', 'password', 'g-recaptcha-response'],
            ],
            [
                ['firstname' => 'A', 'lastname' => 'B'],
                ['email', 'password', 'g-recaptcha-response'],
            ],
            [
                ['firstname' => 'A', 'lastname' => 'B', 'email' => 'invalid_email'],
                ['email', 'password', 'g-recaptcha-response'],
            ],
            [
                ['firstname' => 'A', 'lastname' => 'B', 'email' => 'some_new_user@example.com', 'password' => 'secret123'],
                ['password', 'g-recaptcha-response'],
            ],
            [
                ['firstname' => 'A', 'lastname' => 'B', 'email' => 'some_new_user@example.com', 'password' => 'secret123', 'password_confirmation' => 'secret123'],
                ['g-recaptcha-response'],
            ],
        ];
    }
}
