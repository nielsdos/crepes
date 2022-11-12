<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Session;
use App\Models\SessionGroup;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\CourseDestroyed;
use App\Notifications\CourseEdited;
use App\Services\Settings\ApplicationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testDescriptionMetaTag(): void
    {
        $this->get('/')->assertDontSee('<meta name="description" content', false);
        app(ApplicationSettings::class)->setMainMetaDescription('testing "123"');
        $this->get('/')->assertSee('<meta name="description" content="testing &quot;123&quot;">', false);
        app(ApplicationSettings::class)->setMainMetaDescription('');
        $this->get('/')->assertDontSee('<meta name="description" content', false);
        app(ApplicationSettings::class)->setMainMetaDescription('""<script>""');
        $this->get('/')->assertSee('<meta name="description" content="&quot;&quot;&lt;script&gt;&quot;&quot;">', false);
        app(ApplicationSettings::class)->setMainMetaDescription('😀');
        $this->get('/')->assertSee('<meta name="description" content="😀">', false);
        app(ApplicationSettings::class)->setMainMetaDescription('test\'1\'');
        $this->get('/')->assertSee('<meta name="description" content="test&#039;1&#039;">', false);
        app(ApplicationSettings::class)->setMainMetaDescription('%*+,-/;<=>^|');
        $this->get('/')->assertSee('<meta name="description" content="%*+,-/;&lt;=&gt;^|">', false);
    }

    public function testIndexNoCourses(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSeeText(__('common.no_courses_yet'));
    }

    public function testIndexInvalidYear(): void
    {
        $this->get('/?y=10000')
            ->assertStatus(302)
            ->assertRedirect('/');
    }

    public function testIndexWithCourses(): void
    {
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);

        $sessions = [];
        for ($i = 0; $i < 3; $i++) {
            $baseDate = new Carbon(date('Y', time()).'-11-01');
            $newSessions = Session::factory()
                ->count(2)
                ->for(SessionGroup::factory()->for(Course::factory()->state(['owner_id' => $owner->id])))
                ->state(['start' => $baseDate->addDays(10), 'end' => $baseDate->addDays(11)])
                ->create();
            foreach ($newSessions as $entry) {
                $sessions[] = $entry;
            }
        }

        $response = $this->get('/')
            ->assertStatus(200)
            ->assertDontSeeText(__('common.no_courses_yet'))
            ->assertSeeText($owner->fullName());

        foreach ($sessions as $session) {
            $response->assertSeeText($session->sessionGroup->course->title);
        }

        $this->get('/?y='.((int) date('Y', time()) - 1))
            ->assertStatus(302);

        $applicationSettings = app(ApplicationSettings::class);
        $applicationSettings->setCourseStartMonth(12);
        $this->get('/?y='.((int) date('Y', time()) - 1))
            ->assertDontSeeText(__('common.no_courses_yet'))
            ->assertSeeText($owner->fullName());

        foreach ($sessions as $session) {
            $response->assertSeeText($session->sessionGroup->course->title);
        }

        $applicationSettings->setCourseStartMonth(1);
        $this->get('/?y='.date('Y', time()))
            ->assertDontSeeText(__('common.no_courses_yet'))
            ->assertSeeText($owner->fullName());

        foreach ($sessions as $session) {
            $response->assertSeeText($session->sessionGroup->course->title);
        }
    }

    public function testRenderCreateFailsIfNotLoggedIn(): void
    {
        $this->get(route('course.create'))->assertRedirect(route('login'));
    }

    public function testRenderCreateFailsIfNotAuthorized(): void
    {
        $actingUser = User::factory()->create(['perms' => 0]);
        $this->actingAs($actingUser)->get(route('course.create'))->assertStatus(403);
    }

    public function testRenderCreateSucceeds(): void
    {
        $actingUser = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->actingAs($actingUser)->get(route('course.create'))->assertStatus(200);
    }

    public function testShow(): void
    {
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $session = Session::factory()
            ->for(SessionGroup::factory()->for(Course::factory()->state(['owner_id' => $owner->id])))
            ->state(['start' => Carbon::now()->addDays(10), 'end' => Carbon::now()->addDays(11)])
            ->create();
        $course = $session->sessionGroup->course;

        $this->get(route('course.show', ['course' => $course, 'slug' => 'abc']))
            ->assertRedirect(route('course.show', ['course' => $course, 'slug' => $course->slug]));

        $this->get(route('course.show', ['course' => $course, 'slug' => $course->slug]))
            ->assertStatus(200)
            ->assertViewIs('course.show')
            ->assertViewHas('course', $course)
            ->assertSeeText($course->description);

        $actingUser = User::factory()->create();
        Subscription::create(['user_id' => $actingUser->id, 'session_group_id' => $course->sessionGroups[0]->id]);
        $this->actingAs($actingUser)
            ->get(route('course.show', ['course' => $course, 'slug' => $course->slug]))
            ->assertStatus(200)
            ->assertSeeText(__('common.has_subscribed'));
    }

    private function createTestSessions()
    {
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);

        return Session::factory()
            ->count(5)
            ->for(SessionGroup::factory()->for(Course::factory()->state(['owner_id' => $owner->id])))
            ->state(['start' => Carbon::now()->addDays(10), 'end' => Carbon::now()->addDays(11)])
            ->create();
    }

    public function testEditNotLoggedIn(): void
    {
        $sessions = $this->createTestSessions();
        $course = $sessions[0]->sessionGroup->course;

        $this->get(route('course.edit', ['course' => $course]))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function testEditNotAuthorized(): void
    {
        $sessions = $this->createTestSessions();
        $course = $sessions[0]->sessionGroup->course;
        $actingUser = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);

        $this->actingAs($actingUser)
            ->get(route('course.edit', ['course' => $course]))
            ->assertStatus(403);
    }

    public function testEdit(): void
    {
        $sessions = $this->createTestSessions();
        $course = $sessions[0]->sessionGroup->course;

        $this->actingAs($course->owner)
            ->get(route('course.edit', ['course' => $course]))
            ->assertStatus(200)
            ->assertViewIs('course.edit')
            ->assertViewHas('course', $course);
    }

    public function testDestroyNotLoggedIn(): void
    {
        $sessions = $this->createTestSessions();
        $course = $sessions[0]->sessionGroup->course;

        $this->delete(route('course.destroy', ['course' => $course]))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function testDestroyNotAuthorized(): void
    {
        $sessions = $this->createTestSessions();
        $course = $sessions[0]->sessionGroup->course;
        $actingUser = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);

        $this->actingAs($actingUser)
            ->delete(route('course.destroy', ['course' => $course]))
            ->assertStatus(403);
    }

    public function testDestroy(): void
    {
        \Notification::fake();

        app(ApplicationSettings::class)->setAdminNotificationEmail('notification@example.com');

        $sessions = $this->createTestSessions();
        $course = $sessions[0]->sessionGroup->course;

        $this->actingAs($course->owner)
            ->delete(route('course.destroy', ['course' => $course]))
            ->assertStatus(302);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);

        \Notification::assertSentTo(new AnonymousNotifiable, CourseDestroyed::class, function ($notification, $channels) use ($course) {
            $contents = $notification->toMail(new AnonymousNotifiable)->render();
            $this->assertStringContainsString($course->title, $contents);

            return true;
        });
    }

    public function testStoreNotLoggedIn(): void
    {
        $this->post(route('course.store'), [])
            ->assertRedirect(route('login'));
    }

    public function testStoreNotAuthorized(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post(route('course.store'), [])
            ->assertStatus(403);
    }

    /**
     * @dataProvider storeInputs
     */
    public function testStoreInputs($data, $invalidFields): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $response = $this->actingAs($user)->post(route('course.store'), $data);
        if (! empty($invalidFields)) {
            $response->assertSessionHasErrors($invalidFields);
        }
        $response->assertStatus(302);
    }

    public function testStore(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->assertDatabaseCount('courses', 0);
        $input = last($this->storeInputs())[0];
        $this->actingAs($user)
            ->post(route('course.store'), $input)
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertDatabaseHas('courses', ['title' => $input['course_name']]);
        $course = Course::where('title', '=', $input['course_name'])->first();
        $this->assertNotNull($course);

        $this->assertEquals(2, $course->sessionGroups()->count());
        $this->assertEquals(6, $course->sessions()->count());

        $this->assertEquals($input['group_max_ppl'], array_map(fn ($sg) => $sg['max_ppl'], $course->sessionGroups->toArray()));
        $this->assertEquals(array_merge(...$input['session_location']), array_map(fn ($s) => $s['location'], $course->sessions->toArray()));
    }

    public function testUpdateNotLoggedIn(): void
    {
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $course = Course::factory()->create(['owner_id' => $owner->id]);
        $this->put(route('course.update', ['course' => $course]))
            ->assertRedirect(route('login'));
    }

    public function testUpdateNotAuthorized(): void
    {
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $course = Course::factory()->create(['owner_id' => $owner->id]);
        $user = User::factory()->create();
        $this->actingAs($user)
            ->put(route('course.update', ['course' => $course]))
            ->assertStatus(403);
    }

    public function testUpdateNotAuthorizedNotMyCourse(): void
    {
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $course = Course::factory()->create(['owner_id' => $owner->id]);
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $this->actingAs($user)
            ->put(route('course.update', ['course' => $course]))
            ->assertStatus(403);
    }

    public function testUpdateWrongSessionGroupCount(): void
    {
        \Notification::fake();

        app(ApplicationSettings::class)->setAdminNotificationEmail('notification@example.com');

        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);

        $sessions = Session::factory()
            ->count(2)
            ->for(SessionGroup::factory()
                    ->for(Course::factory()->state(['owner_id' => $owner->id]))
            )
            ->create();
        $course = $sessions[0]->sessionGroup->course;

        $this->actingAs($owner)
            ->put(route('course.update', ['course' => $course]), [])
            ->assertSessionHasErrors('course_name')
            ->assertStatus(302);

        $this->actingAs($owner)
            ->put(route('course.update', ['course' => $course]), [
                'course_name' => 'ABCD',
                'last_date' => '2022-01-01',
                'times' => 5,
                'session_count' => 5,
                'description' => $course->description,
                'desc' => ['a', 'b', 'c', 'd', 'e'],
                'group_max_ppl' => [5, 5, 5, 5, 5],
                'session_location' => array_fill(0, 5, ['a', 'b', 'c', 'd', 'e']),
                'session_starttime' => array_fill(0, 5, ['10:10', '10:10', '10:10', '10:10', '10:10']),
                'session_endtime' => array_fill(0, 5, ['11:10', '11:10', '11:10', '11:10', '11:10']),
                'session_date' => array_fill(0, 5, ['2022-10-10', '2022-10-11', '2022-10-12', '2022-10-13', '2022-10-14']),
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->actingAs($owner)
            ->put(route('course.update', ['course' => $course]), [
                'course_name' => 'ABCD',
                'last_date' => '2022-01-01',
                'times' => 1,
                'session_count' => 5,
                'description' => $course->description,
                'desc' => ['a', 'b', 'c', 'd', 'e'],
                'group_max_ppl' => [5],
                'session_location' => array_fill(0, 1, ['a', 'b', 'c', 'd', 'e']),
                'session_starttime' => array_fill(0, 1, ['10:10', '10:10', '10:10', '10:10', '10:10']),
                'session_endtime' => array_fill(0, 1, ['11:10', '11:10', '11:10', '11:10', '11:10']),
                'session_date' => array_fill(0, 1, ['2022-10-10', '2022-10-11', '2022-10-12', '2022-10-13', '2022-10-14']),
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertDatabaseMissing('courses', ['id' => $course->id, 'title' => 'ABCD']);

        $successData = [
            'course_name' => 'ABCD',
            'last_date' => '2022-01-01',
            'times' => 1,
            'session_count' => 2,
            'description' => $course->description,
            'desc' => ['a', 'b'],
            'group_max_ppl' => [5],
            'session_location' => array_fill(0, 1, ['a', 'b']),
            'session_starttime' => array_fill(0, 1, ['10:10', '10:10']),
            'session_endtime' => array_fill(0, 1, ['11:10', '11:10']),
            'session_date' => array_fill(0, 1, ['2022-10-10', '2022-10-11']),
        ];

        $this->actingAs($owner)
            ->put(route('course.update', ['course' => $course]), $successData)
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'ABCD',
            'last_date' => '2022-01-01',
        ]);

        $this->actingAs($owner)
            ->put(route('course.update', ['course' => $course]), [...$successData, 'save' => '1'])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        \Notification::assertSentTo(new AnonymousNotifiable, CourseEdited::class, function ($notification, $channels) use ($owner) {
            $contents = $notification->toMail($owner)->render();
            $this->assertStringContainsString($owner->fullName(), $contents);

            return true;
        });
    }

    private function _testExportInternalNotLoggedIn(string $suffix): void
    {
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $session = Session::factory()->for(SessionGroup::factory()->for(Course::factory()->state(['owner_id' => $owner->id])))->create();
        $this->get(route('course.export.'.$suffix, ['course' => $session->sessionGroup->course]))
            ->assertRedirect(route('login'));
    }

    private function _testExportInternalNotAuthorized(string $suffix): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_USER]);
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $session = Session::factory()->for(SessionGroup::factory()->for(Course::factory()->state(['owner_id' => $owner->id])))->create();
        $this->actingAs($user)
            ->get(route('course.export.'.$suffix, ['course' => $session->sessionGroup->course]))
            ->assertStatus(403);
    }

    private function _testExportInternal(string $suffix): void
    {
        $owner = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $session = Session::factory()->for(SessionGroup::factory()->for(Course::factory()->state(['owner_id' => $owner->id])))->create();
        $this->actingAs($owner)
            ->get(route('course.export.'.$suffix, ['course' => $session->sessionGroup->course]))
            ->assertDownload('subscribers.'.$suffix);
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

    public function storeInputs(): array
    {
        return [
            [
                [],
                ['course_name'],
            ],
            [
                ['course_name' => 'ABCD'],
                ['last_date'],
            ],
            [
                ['course_name' => 'ABCD', 'last_date' => '12345'],
                ['last_date'],
            ],
            [
                ['course_name' => 'ABCD', 'last_date' => '2022-01-04'],
                ['times'],
            ],
            [
                ['course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 0],
                ['times'],
            ],
            [
                ['course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 0],
                ['session_count'],
            ],
            [
                ['course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3],
                ['description'],
            ],
            [
                ['course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => ''],
                ['description'],
            ],
            [
                ['course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description'],
                ['desc'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => 'wrong type',
                ],
                ['desc'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3', '4', '5', '6'],
                ],
                ['desc'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                ],
                ['group_max_ppl'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => '-5.5',
                ],
                ['group_max_ppl'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'], // This is only checked later, after all non-array stuff has been done, then it goes on to the 2nd layer etc
                    'group_max_ppl' => [1, 2, 3, 4, 5, 6],
                ],
                ['group_max_ppl'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                ],
                ['session_location'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => 123456789,
                ],
                ['session_location'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => 'Some street 1',
                ],
                ['session_location'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => [],
                ],
                ['session_starttime'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                ],
                ['session_endtime'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a'],
                ],
                ['session_endtime'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a', 'b'],
                ],
                ['session_date'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a', 'b'],
                    'session_date' => [],
                ],
                ['session_date'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a', 'b'],
                    'session_date' => [],
                ],
                ['session_date'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a', 'b'],
                    'session_date' => [[]],
                ],
                ['session_date'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', [null], '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a', 'b'],
                    'session_date' => [[], []],
                ],
                ['desc.1'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [-1, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a', 'b'],
                    'session_date' => [[], []],
                ],
                ['group_max_ppl.0'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 2.5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a', 'b'],
                    'session_date' => [[], []],
                ],
                ['group_max_ppl.1'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 5],
                    'session_location' => ['Some street 1', 'Some street 1'],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a', 'b'],
                    'session_date' => [[], []],
                ],
                ['session_location.0'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 5],
                    'session_location' => [['Some street 1', 'Some street 1'], ['Some street 2', 'Some street 2']],
                    'session_starttime' => ['a', 'b'],
                    'session_endtime' => ['a', 'b'],
                    'session_date' => [[], []],
                ],
                ['session_location.0'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 5],
                    'session_location' => [['Some street 1', 'Some street 1', 'Some street 1'], ['Some street 2', 'Some street 2', 'Some street 2']],
                    'session_starttime' => [['10:10', '10:10', '10:10'], ['09:10', '09:10', '09:10']],
                    'session_endtime' => ['a', 'b'],
                    'session_date' => [[], []],
                ],
                ['session_endtime.0'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 5],
                    'session_location' => [['Some street 1', 'Some street 1', 'Some street 1'], ['Some street 2', 'Some street 2', 'Some street 2']],
                    'session_starttime' => [['10:10', '10:10', '10:10'], ['09:10', '09:10', '09:10']],
                    'session_endtime' => [['11:10', '10:10', '10:10'], ['09:10', '09:10', '09:10']],
                    'session_date' => [[], []],
                ],
                ['session_starttime.0.1'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 5],
                    'session_location' => [['Some street 1', 'Some street 1', 'Some street 1'], ['Some street 2', 'Some street 2', 'Some street 2']],
                    'session_starttime' => [['10:10', '10:10', '10:10'], ['09:10', '09:10', '09:10']],
                    'session_endtime' => [['11:10', '11:10', '12:10'], ['09:50', '09:55', '09:60']],
                    'session_date' => [[], []],
                ],
                ['session_endtime.1.2'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 5],
                    'session_location' => [['Some street 1', 'Some street 1', 'Some street 1'], ['Some street 2', 'Some street 2', 'Some street 2']],
                    'session_starttime' => [['10:10', '10:10', '10:10'], ['09:10', '09:10', '09:10']],
                    'session_endtime' => [['11:10', '11:10', '12:10'], ['09:50', '09:55', '10:00']],
                    'session_date' => [[], []],
                ],
                ['session_date.0'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 5],
                    'session_location' => [['Some street 1', 'Some street 1', 'Some street 1'], ['Some street 2', 'Some street 2', 'Some street 2']],
                    'session_starttime' => [['10:10', '10:10', '10:10'], ['09:10', '09:10', '09:10']],
                    'session_endtime' => [['11:10', '11:10', '12:10'], ['09:50', '09:55', '10:00']],
                    'session_date' => [['2022-10-10', '2022-10-11', '2022-10-09'], []],
                ],
                ['session_date.1'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 5],
                    'session_location' => [['Some street 1', 'Some street 1', 'Some street 1'], ['Some street 2', 'Some street 2', 'Some street 2']],
                    'session_starttime' => [['10:10', '10:10', '10:10'], ['09:10', '09:10', '09:10']],
                    'session_endtime' => [['11:10', '11:10', '12:10'], ['09:50', '09:55', '10:00']],
                    'session_date' => [['2022-10-10', '2022-10-11', '2022-10-09'], ['2022-10-10', '2022-10-11', '2022-10-09']],
                ],
                ['session_date.0.1'],
            ],
            [
                [
                    'course_name' => 'ABCD', 'last_date' => '2022-01-04', 'times' => 2, 'session_count' => 3, 'description' => 'This is a description',
                    'desc' => ['1', '2', '3'],
                    'group_max_ppl' => [5, 5],
                    'session_location' => [['Some street 1', 'Some street 1', 'Some street 1'], ['Some street 2', 'Some street 2', 'Some street 2']],
                    'session_starttime' => [['10:10', '10:10', '10:10'], ['09:10', '09:10', '09:10']],
                    'session_endtime' => [['11:10', '11:10', '12:10'], ['09:50', '09:55', '10:00']],
                    'session_date' => [['2022-10-10', '2022-10-11', '2022-10-12'], ['2022-11-10', '2022-11-11', '2022-11-12']],
                ],
                [],
            ],
        ];
    }
}
