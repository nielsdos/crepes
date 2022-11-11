<?php

namespace App\Services;

final class CourseDependentCache
{
    public const TAG = 'course-dependent';

    public static function flush(): void
    {
        CacheUtility::flushTagIfMayCache(self::TAG);
    }

    public static function rememberTaggedForeverIfMayCache(string $key, callable $callback): mixed
    {
        return CacheUtility::rememberTaggedForeverIfMayCache(['view-dependent', self::TAG], $key, $callback);
    }
}
