<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crepes:create-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $email = $this->ask('What is the desired e-mail address?');

        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first('email'));

            return Command::FAILURE;
        }

        $firstName = $this->ask('What is the first name of the user?', 'First');
        $lastName = $this->ask('What is the last name of the user?', 'Last');

        $password = $this->secret('Enter a password? (What you type will be hidden)');
        $passwordConfirm = $this->secret('Repeat your password? (What you type will be hidden)');

        if ($password !== $passwordConfirm) {
            $this->error('Passwords do not match. Please try again.');

            return Command::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Password too short.');

            return Command::FAILURE;
        }

        $permsChoices = ['admin', 'course manager', 'no permissions (regular user)'];
        $perms = $this->choice('What permissions do you want this user to have?', $permsChoices, last($permsChoices));
        $permsNr = match ($perms) {
            'admin' => User::PERMS_ADMIN,
            'course manager' => User::PERMS_COURSE_MANAGER,
            default => User::PERMS_USER,
        };

        $reminders = $this->confirm('Does this user want to receive reminders?');

        User::create([
            'email' => $email,
            'email_verified_at' => now(),
            'firstname' => $firstName,
            'lastname' => $lastName,
            'perms' => $permsNr,
            'password' => \Hash::make($password),
            'reminders' => $reminders,
        ]);

        $this->info('User created successfully.');

        return Command::SUCCESS;
    }
}
