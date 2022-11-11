<?php

namespace Tests\Feature;

use App\Services\Settings\ApplicationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyPolicyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSee()
    {
        $mock = $this->createMock(ApplicationSettings::class);
        $mock->method('getPrivacyPolicy')->willReturn('<b>hi</b>');
        $this->instance(ApplicationSettings::class, $mock);
        $response = $this->get(route('privacy'));
        $response->assertStatus(200);
        $response->assertSee('<b>hi</b>', false);
    }
}
