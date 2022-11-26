<?php

namespace Tests\Feature;

use Tests\TestCase;

class CourseIndexControllerTest extends TestCase
{
    public function test(): void
    {
        $routeCustomizedNames = config('app.route_customized_names');
        $response = $this->get($routeCustomizedNames['course']);
        $response->assertStatus(302)->assertRedirect(route('course.index'));
    }
}
