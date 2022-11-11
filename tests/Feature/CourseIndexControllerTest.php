<?php

namespace Tests\Feature;

use Tests\TestCase;

class CourseIndexControllerTest extends TestCase
{
    public function test(): void
    {
        $response = $this->get('/course');
        $response->assertStatus(302)->assertRedirect(route('course.index'));
    }
}
