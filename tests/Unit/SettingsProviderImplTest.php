<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\Settings\SettingsProviderImpl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsProviderImplTest extends TestCase
{
    use RefreshDatabase;

    public function testGetNonExistent(): void
    {
        $provider = new SettingsProviderImpl();
        $this->assertEquals(0, $provider->get('nonexistent', 0));
        $this->assertEquals(0, $provider->get('nonexistent', 'a'));
        $provider->set('existent', '123');
        $this->assertEquals(0, $provider->get('nonexistent', 1));
        $this->assertEquals(0, $provider->get('nonexistent', 'b'));
    }

    public function testSet(): void
    {
        $provider = new SettingsProviderImpl();
        $this->assertFalse(Setting::where('key', '=', 'test_set')->exists());
        $provider->set('test_set', 'abcdef');
        $query = Setting::where('key', '=', 'test_set');
        $this->assertTrue($query->exists());
        $model = $query->first();
        $this->assertEquals('abcdef', $model->value);
        $provider->set('test_set', 'xyz');
        $model->refresh();
        $this->assertEquals('xyz', $model->value);
        $provider->set('test_set', false);
        $model->refresh();
        $this->assertEquals('', $model->value);
        $provider->set('test_set', true);
        $model->refresh();
        $this->assertEquals('1', $model->value);
    }
}
