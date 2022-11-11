<?php

namespace App\Services\Settings;

use App\Models\Setting;
use App\Services\CacheUtility;

final class SettingsProviderImpl implements SettingsProvider
{
    private bool $shouldCache;

    public function __construct()
    {
        $this->shouldCache = CacheUtility::shouldCache();
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string $key, string|int|bool $default): string|int|bool
    {
        if ($this->shouldCache) {
            $value = \Cache::get('setting.'.$key);
            if ($value !== null) {
                return $value;
            }
        }

        $value = Setting::where('key', '=', $key)->first()->value ?? $default;

        if ($this->shouldCache) {
            \Cache::set('setting.'.$key, $value);
        }

        return $value;
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set(string $key, string $value): void
    {
        if ($this->shouldCache) {
            \Cache::set('setting.'.$key, $value);
        }

        Setting::updateOrInsert(compact('key'), compact('value'));
    }
}
