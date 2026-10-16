<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Throwable;

final class MarketplaceCache
{
    private const VERSION_KEY = 'marketplace:catalog:version';
    private const KEY_LIST = 'marketplace:catalog:keys';
    private const MAX_KEYS = 100;

    public static function version(): int
    {
        try {
            Cache::add(self::VERSION_KEY, 1, now()->addYears(10));

            return (int) Cache::get(self::VERSION_KEY, 1);
        } catch (Throwable $exception) {
            report($exception);

            return 1;
        }
    }

    public static function track(string $key): void
    {
        try {
            $keys = Cache::get(self::KEY_LIST, []);
            $keys = is_array($keys) ? array_values(array_filter($keys, 'is_string')) : [];
            $keys = array_values(array_diff($keys, [$key]));
            $keys[] = $key;

            while (count($keys) > self::MAX_KEYS) {
                Cache::forget(array_shift($keys));
            }

            Cache::forever(self::KEY_LIST, $keys);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public static function invalidate(): void
    {
        try {
            self::version();
            Cache::increment(self::VERSION_KEY);
        } catch (Throwable $exception) {
            report($exception);
        }

        try {
            $keys = Cache::pull(self::KEY_LIST, []);
            foreach (is_array($keys) ? $keys : [] as $key) {
                if (is_string($key)) {
                    Cache::forget($key);
                }
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}