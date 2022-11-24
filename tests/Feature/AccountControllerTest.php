<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ChangeEmailConfirmation;
use App\Notifications\ChangeEmailOld;
use App\Notifications\PasswordChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexNotLoggedIn(): void
    {
        $this->get(route('account.index'))
            ->assertRedirect(route('login'));
    }

    public function testIndexNotAuthorized(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->actingAs($user)
            ->get(route('account.index'))
            ->assertForbidden();
    }

    public function testIndexNoSearchQuery(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $users = User::factory()->count(10)->create();
        $response = $this->actingAs($user)
            ->get(route('account.index'))
            ->assertOk()
            ->assertViewIs('account.index');
        $this->assertCount($users->count() + 1, $response->viewData('users'));
        foreach ($users as $user) {
            $response->assertSeeText($user->firstname);
            $response->assertSeeText($user->lastname);
        }
    }

    public function testIndexInvalidSearchQuery(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $users = User::factory()->count(10)->create();
        $response = $this->actingAs($user)
            ->get(route('account.index').'?q=a**')
            ->assertOk()
            ->assertViewIs('account.index')
            ->assertViewHas('hasSyntaxError', true);
        $this->assertCount($users->count() + 1, $response->viewData('users'));
        foreach ($users as $user) {
            $response->assertSeeText($user->firstname);
            $response->assertSeeText($user->lastname);
        }
    }

    public function testIndexMultiPageSearch(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        User::factory()->count(30)->state(['firstname' => 'abc'])->create();
        $this->actingAs($user)
            ->get(route('account.index').'?q=abc&page=2')
            ->assertOk()
            ->assertViewIs('account.index')
            ->assertViewHas('hasSyntaxError', false)
            ->assertDontSeeText(__('common.no_results'));
    }

    private function _testExportInternalNotLoggedIn(string $suffix): void
    {
        $this->get(route('account.export.'.$suffix))
            ->assertRedirect(route('login'));
    }

    private function _testExportInternalNotAuthorized(string $suffix): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->actingAs($user)
            ->get(route('account.export.'.$suffix))
            ->assertForbidden();
    }

    private function _testExportInternal(string $suffix): void
    {
        $admin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $users = User::factory()->count(30)->create();
        $response = $this->actingAs($admin)
            ->get(route('account.export.'.$suffix))
            ->assertDownload('accounts.'.$suffix);

        // And actually check the contents of the file
        $spreadsheet = IOFactory::load($response->getFile()->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $gottenEmails = array_map(fn ($entry) => $entry[2], $sheet->toArray());
        array_shift($gottenEmails);

        foreach ($users as $user) {
            $this->assertContains($user->email, $gottenEmails);
        }
    }

    public function testExportCsvNotLoggedIn(): void
    {
        $this->_testExportInternalNotLoggedIn('csv');
    }

    public function testExportCsvNotAuthorized(): void
    {
        $this->_testExportInternalNotAuthorized('csv');
    }

    public function testExportCsv(): void
    {
        $this->_testExportInternal('csv');
    }

    public function testExportExcelNotLoggedIn(): void
    {
        $this->_testExportInternalNotLoggedIn('xlsx');
    }

    public function testExportExcelNotAuthorized(): void
    {
        $this->_testExportInternalNotAuthorized('xlsx');
    }

    public function testExportExcel(): void
    {
        $this->_testExportInternal('xlsx');
    }

    public function testForgetNotLoggedIn(): void
    {
        $this->post(route('account.forget'))
            ->assertRedirect(route('login'));
    }

    public function testForget(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post(route('account.forget'))
            ->assertRedirect('/');
        $this->assertTrue($user->trashed());
        $this->assertNull($user->email_verified_at);
    }

    public function testDestroyNotLoggedIn(): void
    {
        $victim = User::factory()->create();
        $this->delete(route('account.destroy', ['user' => $victim]))
            ->assertRedirect(route('login'));
    }

    public function testDestroyNotAuthorized(): void
    {
        $notAdmin = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $victim = User::factory()->create();
        $this->actingAs($notAdmin)
            ->delete(route('account.destroy', ['user' => $victim]))
            ->assertForbidden();
    }

    public function testDestroy(): void
    {
        $admin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $victim = User::factory()->create();
        $this->actingAs($admin)
            ->delete(route('account.destroy', ['user' => $victim]))
            ->assertRedirect(route('account.index'));
        $this->assertDatabaseMissing('users', ['id' => $victim->id]);
    }

    public function testDestroyNotExisting(): void
    {
        $admin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->assertDatabaseCount('users', 1);
        $this->actingAs($admin)
            ->delete(route('account.destroy', ['user' => 42]))
            ->assertNotFound();
        $this->assertDatabaseCount('users', 1);
    }

    public function testDestroyCantSelf(): void
    {
        $admin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->assertDatabaseCount('users', 1);
        $this->actingAs($admin)
            ->delete(route('account.destroy', ['user' => $admin]))
            ->assertRedirect('/');
        $this->assertDatabaseCount('users', 1);
    }

    public function testEditNotLoggedIn(): void
    {
        $user = User::factory()->create();
        $this->get(route('account.edit', ['user' => $user]))
            ->assertRedirect(route('login'));
    }

    public function testEditNotAuthorized(): void
    {
        $user = User::factory()->create();
        $notAdmin = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->actingAs($notAdmin)
            ->get(route('account.edit', ['user' => $user]))
            ->assertForbidden();
    }

    public function testEditUserAsAdmin(): void
    {
        $admin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $user = User::factory()->create();
        $this->actingAs($admin)
            ->get(route('account.edit', ['user' => $user]))
            ->assertOk()
            ->assertViewIs('account.edit')
            ->assertViewHas('user', $user);
    }

    public function testEditUserNotExistingAsAdmin(): void
    {
        $admin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->actingAs($admin)
            ->get(route('account.edit', ['user' => 42]))
            ->assertRedirect(route('account.index'));
    }

    public function testEditMe(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('account.edit', ['user' => 'me']))
            ->assertOk()
            ->assertViewIs('account.edit')
            ->assertViewHas('user', $user);
    }

    public function testUpdatePersonalNotLoggedIn(): void
    {
        $user = User::factory()->create();
        $this->put(route('account.update.personal', ['user' => $user]))
            ->assertRedirect(route('login'));
    }

    public function testUpdatePersonalNotAuthorized(): void
    {
        $user = User::factory()->create();
        $notAdmin = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->actingAs($notAdmin)
            ->put(route('account.update.personal', ['user' => $user]))
            ->assertForbidden();
    }

    /**
     * @dataProvider updatePersonalInputs
     */
    public function testUpdatePersonalInvalidData($data, $invalidFields): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->put(route('account.update.personal', ['user' => 'me']), $data)
            ->assertSessionHasErrors($invalidFields)
            ->assertStatus(302);
    }

    public function testUpdatePersonalCorrectData(): void
    {
        $user = User::factory()->create();
        $data = [
            'firstname' => 'A',
            'lastname' => 'B',
            'reminders' => true,
            'function' => 'some function',
            'member_nr' => 'some member nr',
        ];
        $this->actingAs($user)
            ->put(route('account.update.personal', ['user' => 'me']), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirectContains('#personal');
        $this->assertDatabaseHas('users', $data);
        $this->actingAs($user)
            ->put(route('account.update.personal', ['user' => 'me']), [...$data, 'save' => true])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');
        $this->assertDatabaseHas('users', $data);
    }

    public function testUpdatePasswordNotLoggedIn(): void
    {
        $user = User::factory()->create();
        $this->put(route('account.update.password', ['user' => $user]))
            ->assertRedirect(route('login'));
    }

    public function testUpdatePasswordNotAuthorized(): void
    {
        $user = User::factory()->create();
        $notAdmin = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->actingAs($notAdmin)
            ->put(route('account.update.password', ['user' => $user]))
            ->assertForbidden();
    }

    public function testUpdatePasswordAsAdminForbidden(): void
    {
        $user = User::factory()->create();
        $notAdmin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->actingAs($notAdmin)
            ->put(route('account.update.password', ['user' => $user]))
            ->assertForbidden();
    }

    /**
     * @dataProvider updatePasswordInputs
     */
    public function testUpdatePasswordInvalidInputs($data, $invalidFields): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->put(route('account.update.password', ['user' => 'me']), $data)
            ->assertSessionHasErrors($invalidFields)
            ->assertStatus(302);
    }

    public function testUpdatePasswordCorrect(): void
    {
        \Notification::fake();

        $user = User::factory()->create();
        $this->actingAs($user)
            ->put(route('account.update.password', ['user' => 'me']), [
                'password_current_password' => 'secret',
                'password' => 'new_password',
                'password_confirmation' => 'new_password',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        \Notification::assertSentTo($user, PasswordChanged::class, function ($notification, $channels) use ($user) {
            $contents = $notification->toMail($user)->render();
            $this->assertStringContainsString($user->fullName(), $contents);

            return true;
        });
    }

    public function testUpdateEmailNotLoggedIn(): void
    {
        $user = User::factory()->create();
        $this->put(route('account.update.email', ['user' => $user]))
            ->assertRedirect(route('login'));
    }

    public function testUpdateEmailNotAuthorized(): void
    {
        $user = User::factory()->create();
        $notAdmin = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->actingAs($notAdmin)
            ->put(route('account.update.email', ['user' => $user]))
            ->assertForbidden();
    }

    public function testUpdateEmailAsAdminForbidden(): void
    {
        $user = User::factory()->create();
        $notAdmin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->actingAs($notAdmin)
            ->put(route('account.update.email', ['user' => $user]))
            ->assertForbidden();
    }

    /**
     * @dataProvider updateEmailInputs
     */
    public function testUpdateEmailInvalidInputs($data, $invalidFields): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->put(route('account.update.email', ['user' => 'me']), $data)
            ->assertSessionHasErrors($invalidFields)
            ->assertStatus(302);
    }

    public function testUpdateEmailInvalidInputsExistingEmail(): void
    {
        $existingUser = User::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user)
            ->put(route('account.update.email', ['user' => 'me']), [
                'email_current_password' => 'secret',
                'email' => $existingUser->existingEmail,
                'email_confirmation' => $existingUser->existingEmail,
            ])
            ->assertSessionHasErrors('email')
            ->assertStatus(302);
    }

    public function testUpdateEmailToTheSameEmail(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->put(route('account.update.email', ['user' => 'me']), [
                'email_current_password' => 'secret',
                'email' => $user->email,
                'email_confirmation' => $user->email,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', __('acts.email_change_same'))
            ->assertStatus(302);
    }

    public function testUpdateEmailCorrect(): void
    {
        \Notification::fake();

        $email = 'new_mail_address@example.com';
        $user = User::factory()->create();
        $this->actingAs($user)
            ->put(route('account.update.email', ['user' => 'me']), [
                'email_current_password' => 'secret',
                'email' => $email,
                'email_confirmation' => $email,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', __('acts.email_change_msg', ['email' => $email]))
            ->assertStatus(302);

        \Notification::assertSentTo($user, ChangeEmailConfirmation::class, function ($notification, $channels) use ($user, $email) {
            $url = $notification->toMail($user)->toArray()['actionUrl'];

            // Sad flow: Must be logged in as the correct user
            $this->actingAs(User::factory()->create())->get($url)->assertStatus(302);
            $this->assertDatabaseMissing('users', ['id' => $user->id, 'email' => $email]);

            // Happy flow
            $this->actingAs($user)->get($url)->assertSessionHas('success')->assertStatus(302);
            $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => $email]);

            return true;
        });

        \Notification::assertSentTo(new AnonymousNotifiable, ChangeEmailOld::class, function ($notification, $channels) use ($user, $email) {
            $contents = $notification->toMail($user)->render();
            $this->assertStringContainsString($user->fullName(), $contents);
            $this->assertStringContainsString($email, $contents);

            return true;
        });
    }

    public function testUpdateAdminNotLoggedIn(): void
    {
        $user = User::factory()->create();
        $this->put(route('account.update.admin', ['user' => $user]))
            ->assertRedirect(route('login'));
    }

    public function testUpdateAdminNotAuthorized(): void
    {
        $user = User::factory()->create();
        $notAdmin = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->actingAs($notAdmin)
            ->put(route('account.update.admin', ['user' => $user]))
            ->assertForbidden();
    }

    /**
     * @dataProvider updateAdminInputs
     */
    public function testUpdateAdminInvalidInputs($data, $invalidFields): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->actingAs($admin)
            ->put(route('account.update.admin', ['user' => $user]), $data)
            ->assertSessionHasErrors($invalidFields)
            ->assertStatus(302);
    }

    public function testUpdateAdminInvalidEmail(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->actingAs($admin)
            ->put(route('account.update.admin', ['user' => $user]), ['role' => 5, 'admin_email' => $admin->email])
            ->assertSessionHasErrors('admin_email')
            ->assertStatus(302);
    }

    public function testUpdateAdminCorrect(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $admin = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->actingAs($admin)
            ->put(route('account.update.admin', ['user' => $user]), ['role' => 5, 'admin_email' => $user->email, 'verifiedCheck' => true])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', __('acts.saved'))
            ->assertStatus(302);

        $this->actingAs($admin)
            ->put(route('account.update.admin', ['user' => $user]), ['role' => 5, 'admin_email' => $user->email, 'verifiedCheck' => true])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', __('acts.saved'))
            ->assertStatus(302);

        $this->actingAs($admin)
            ->put(route('account.update.admin', ['user' => $user]), ['role' => 5, 'admin_email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', __('acts.saved'))
            ->assertStatus(302);

        $this->actingAs($admin)
            ->put(route('account.update.admin', ['user' => $admin]), ['role' => User::PERMS_ADMIN, 'admin_email' => $admin->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', __('acts.saved'))
            ->assertStatus(302);
    }

    public static function updatePersonalInputs(): array
    {
        return [
            [
                [],
                ['firstname', 'lastname'],
            ],
            [
                [
                    'firstname' => 'John',
                    'lastname' => '',
                ],
                ['lastname'],
            ],
        ];
    }

    public static function updatePasswordInputs(): array
    {
        return [
            [
                [],
                ['password_current_password', 'password'],
            ],
            [
                ['password_current_password' => 'nope'],
                ['password_current_password', 'password'],
            ],
            [
                ['password_current_password' => 'secret'],
                ['password'],
            ],
            [
                ['password_current_password' => 'secret', 'password' => 'new_password'],
                ['password'],
            ],
        ];
    }

    public static function updateEmailInputs(): array
    {
        return [
            [
                [],
                ['email_current_password', 'email'],
            ],
            [
                ['email_current_password' => 'nope'],
                ['email_current_password', 'email'],
            ],
            [
                ['email_current_password' => 'secret'],
                ['email'],
            ],
            [
                ['email_current_password' => 'secret', 'email' => 'test'],
                ['email'],
            ],
        ];
    }

    public static function updateAdminInputs(): array
    {
        return [
            [
                [],
                ['admin_email', 'role'],
            ],
            [
                ['role' => 3],
                ['admin_email', 'role'],
            ],
            [
                ['role' => 5],
                ['admin_email'],
            ],
            [
                ['role' => 5, 'admin_email' => 'invalid_email'],
                ['admin_email'],
            ],
        ];
    }
}
