<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    private array $permissionChoices = ['admin', 'course manager', 'no permissions (regular user)'];

    private function createSuccessHelper(string $perms): void
    {
        $this->artisan('crepes:create-user')
            ->expectsQuestion('What is the desired e-mail address?', 'test@example.com')
            ->expectsQuestion('What is the first name of the user?', 'Random')
            ->expectsQuestion('What is the last name of the user?', 'Name')
            ->expectsQuestion('Enter a password? (What you type will be hidden)', 'password')
            ->expectsQuestion('Repeat your password? (What you type will be hidden)', 'password')
            ->expectsChoice('What permissions do you want this user to have?', $perms, $this->permissionChoices)
            ->expectsConfirmation('Does this user want to receive reminders?', 'yes')
            ->expectsOutput('User created successfully.')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'firstname' => 'Random',
            'lastname' => 'Name',
            'perms' => match ($perms) {
                'admin' => User::PERMS_ADMIN,
                'course manager' => User::PERMS_COURSE_MANAGER,
                default => User::PERMS_USER,
            },
            'reminders' => true,
        ]);
    }

    public function testCreateSuccess0(): void
    {
        $this->createSuccessHelper($this->permissionChoices[0]);
    }

    public function testCreateSuccess1(): void
    {
        $this->createSuccessHelper($this->permissionChoices[1]);
    }

    public function testCreateSuccess2(): void
    {
        $this->createSuccessHelper($this->permissionChoices[2]);
    }

    public function testCreateFailEmailInvalid(): void
    {
        $this->artisan('crepes:create-user')
            ->expectsQuestion('What is the desired e-mail address?', 'azerty')
            ->doesntExpectOutput('User created successfully.')
            ->assertFailed();
    }

    public function testCreateFailPasswordMismatch(): void
    {
        $this->artisan('crepes:create-user')
            ->expectsQuestion('What is the desired e-mail address?', 'test@example.com')
            ->expectsQuestion('What is the first name of the user?', 'Random')
            ->expectsQuestion('What is the last name of the user?', 'Name')
            ->expectsQuestion('Enter a password? (What you type will be hidden)', 'a')
            ->expectsQuestion('Repeat your password? (What you type will be hidden)', 'b')
            ->doesntExpectOutput('User created successfully.')
            ->assertFailed();
    }

    public function testCreateFailPasswordTooShort(): void
    {
        $this->artisan('crepes:create-user')
            ->expectsQuestion('What is the desired e-mail address?', 'test@example.com')
            ->expectsQuestion('What is the first name of the user?', 'Random')
            ->expectsQuestion('What is the last name of the user?', 'Name')
            ->expectsQuestion('Enter a password? (What you type will be hidden)', '1234')
            ->expectsQuestion('Repeat your password? (What you type will be hidden)', '1234')
            ->doesntExpectOutput('User created successfully.')
            ->assertFailed();
    }
}
