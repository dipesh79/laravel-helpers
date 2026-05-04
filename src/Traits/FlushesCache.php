<?php

namespace Dipesh79\LaravelHelpers\Traits;

use Illuminate\Support\Facades\Cache;

trait FlushesCache
{
    public static function bootFlushesCache(): void
    {
        static::created(fn() => static::flushModelCache());
        static::updated(fn() => static::flushModelCache());
        static::deleted(fn() => static::flushModelCache());
    }

    protected static function flushModelCache(): void
    {
        $tags = static::getCacheTags();

        if (!empty($tags)) {
            Cache::tags($tags)->flush();
            return;
        }

        Cache::flush();
    }

    /**
     * Resolve model cache tags if defined
     */
    protected static function getCacheTags(): array
    {
        if (property_exists(static::class, 'cacheTags')) {
            return static::$cacheTags ?? [];
        }
        return [];
    }
}