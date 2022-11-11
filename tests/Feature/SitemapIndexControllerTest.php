<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSitemap(): void
    {
        $user = User::factory()->create(['perms' => User::PERMS_COURSE_MANAGER]);
        $courses = Course::factory()->count(10)->create(['owner_id' => $user->id]);
        for ($i = 0; $i < $courses->count(); $i++) {
            $courses[$i]->last_date = Carbon::now()->subDays($i * 300);
            $courses[$i]->save();
        }

        $response = $this->get('/sitemap.xml')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        foreach ($courses as $course) {
            $response->assertSee($course->slug);
        }
    }
}
