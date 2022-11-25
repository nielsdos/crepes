<?php

namespace Tests\Unit;

use App\Services\CacheUtility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class CacheUtilityTest extends TestCase
{
    use RefreshDatabase;

    public function testShouldCache()
    {
        Cache::setDefaultDriver('database');
        $this->assertFalse(CacheUtility::shouldCache());
        Cache::setDefaultDriver('array');
        $this->assertTrue(CacheUtility::shouldCache());
    }

    public function testShouldCacheTaggedData()
    {
        Cache::setDefaultDriver('database');
        $this->assertFalse(CacheUtility::shouldCacheTaggedData());
        Cache::setDefaultDriver('array');
        $this->assertTrue(CacheUtility::shouldCacheTaggedData());
    }

    public function testRememberTaggedForeverIfMayCache()
    {
        Cache::setDefaultDriver('file');
        $ret = CacheUtility::rememberTaggedForeverIfMayCache('test-tag', 'test-key', function () {
            return 'test-value';
        });
        $this->assertEquals('test-value', $ret);
        $this->assertNull(Cache::get('test-key'));
        Cache::setDefaultDriver('array');
        $ret = CacheUtility::rememberTaggedForeverIfMayCache('test-tag', 'test-key', function () {
            return 'test-value';
        });
        $this->assertEquals('test-value', $ret);
        $this->assertEquals('test-value', Cache::tags('test-tag')->get('test-key'));
    }

    public function testFlushTagIfMayCache()
    {
        Cache::setDefaultDriver('array');
        Cache::tags('test')->put('test-key', 'test-value');
        CacheUtility::shouldCacheTaggedData();
        $this->assertEquals('test-value', Cache::tags('test')->get('test-key'));
    }
}
