<?php

namespace App\Services;

final class CacheUtility
{
    public static function shouldCache(): bool
    {
        return \Cache::getDefaultDriver() !== 'database';
    }

    public static function shouldCacheTaggedData(): bool
    {
        // NOTE: database does not support tags, so this implicitly checks the same condition as shouldCache().
        return \Cache::supportsTags();
    }

    /**
     * @param  string|array<string>  $tag
     * @param  string  $key
     * @param  callable  $callback
     * @return mixed
     */
    public static function rememberTaggedForeverIfMayCache(string|array $tag, string $key, callable $callback): mixed
    {
        if (self::shouldCacheTaggedData()) {
            return \Cache::tags($tag)->rememberForever($key, $callback);
        }

        return $callback();
    }

    /**
     * @param  string|array<string>  $tag
     * @return void
     */
    public static function flushTagIfMayCache(string|array $tag): void
    {
        if (self::shouldCacheTaggedData()) {
            \Cache::tags($tag)->flush();
        }
    }
}
