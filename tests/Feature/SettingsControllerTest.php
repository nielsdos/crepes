<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testEditNotLoggedIn(): void
    {
        $response = $this->get(route('settings.edit'));
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testEditNotAuthorized(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $response = $this->actingAs($user)->get(route('settings.edit'));
        $response->assertStatus(403);
    }

    public function testEdit(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $response = $this->actingAs($user)->get(route('settings.edit'));
        $response->assertStatus(200);
        $response->assertViewIs('settings.edit');
        $response->assertViewHas('course_start_month', 1);
        $response->assertViewHas('course_overlap_months', 0);
        $response->assertViewHas('privacy_policy_html', '');
        $response->assertViewHas('main_meta_description', '');
        $response->assertViewHas('admin_notification_email', '');
    }

    public function testUpdateViewNotLoggedIn(): void
    {
        $response = $this->put(route('settings.update.view'));
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testUpdateViewNotAuthorized(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $response = $this->actingAs($user)->put(route('settings.update.view'));
        $response->assertStatus(403);
    }

    /**
     * @dataProvider invalidViewInputs
     */
    public function testUpdateViewInvalidInputs($invalidData, $invalidFields): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->actingAs($user)
            ->put(route('settings.update.view', $invalidData))
            ->assertSessionHasErrors($invalidFields)
            ->assertStatus(302);
    }

    public function invalidViewInputs(): array
    {
        return [
            [
                [],
                ['course_start_month', 'course_overlap_months'],
            ],
            [
                ['course_start_month' => 1],
                ['course_overlap_months'],
            ],
            [
                ['course_overlap_months' => 5],
                ['course_start_month'],
            ],
            [
                ['course_start_month' => -1, 'course_overlap_months' => 13],
                ['course_start_month', 'course_overlap_months'],
            ],
            [
                ['course_start_month' => -5, 'course_overlap_months' => 11],
                ['course_start_month'],
            ],
            [
                ['course_start_month' => 2, 'course_overlap_months' => 12],
                ['course_overlap_months'],
            ],
        ];
    }

    public function testUpdateView(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $this->actingAs($user)->put(route('settings.update.view', [
            'course_start_month' => 9,
            'course_overlap_months' => 2,
            'main_meta_description' => 'Hello there<!>',
        ]))
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $this->assertDatabaseHas('settings', [
            'key' => 'course_start_month',
            'value' => 9,
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'course_overlap_months',
            'value' => 2,
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'main_meta_description',
            'value' => 'Hello there<!>',
        ]);
    }

    public function testUpdatePrivacyPolicyNotLoggedIn(): void
    {
        $response = $this->put(route('settings.update.privacy'));
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testUpdatePrivacyPolicyNotAuthorized(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $response = $this->actingAs($user)->put(route('settings.update.privacy'));
        $response->assertStatus(403);
    }

    public function testUpdatePrivacyPolicy(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $response = $this->actingAs($user)->put(route('settings.update.privacy', [
        ]));
        $response
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $response = $this->actingAs($user)->put(route('settings.update.privacy', [
            'privacy_policy_html' => '',
        ]));
        $response
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $response = $this->actingAs($user)->put(route('settings.update.privacy', [
            'privacy_policy_html' => '<p>hi<script>alert(1)</script></p><a onerror="alert(1)" class="x" href="https://google.com" test>123</a>',
            'course_overlap_months' => 0,
        ]));
        $response
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $this->assertDatabaseHas('settings', ['key' => 'privacy_policy', 'value' => '<p>hi</p><a href="https://google.com" target="_blank" rel="noreferrer noopener">123</a>']);
    }

    public function testUpdateOptionsNotLoggedIn(): void
    {
        $response = $this->put(route('settings.update.options'));
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testUpdateOptionsNotAuthorized(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $response = $this->actingAs($user)->put(route('settings.update.options'));
        $response->assertStatus(403);
    }

    public function testUpdateOptions(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_ADMIN]);
        $response = $this->actingAs($user)->put(route('settings.update.options', [
        ]));
        $response
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $response = $this->actingAs($user)->put(route('settings.update.options', [
            'admin_notification_email' => '',
        ]));
        $response
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $response = $this->actingAs($user)->put(route('settings.update.options', [
            'admin_notification_email' => 'invalidemail',
        ]));
        $response
            ->assertSessionHasErrors('admin_notification_email')
            ->assertStatus(302);
        $response = $this->actingAs($user)->put(route('settings.update.options', [
            'admin_notification_email' => 'test@example.com',
        ]));
        $response
            ->assertSessionHasNoErrors()
            ->assertStatus(302);
        $this->assertDatabaseHas('settings', ['key' => 'admin_notification_email', 'value' => 'test@example.com']);
    }
}
