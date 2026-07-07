<?php

namespace App\Support;

final class RailwayAppConfig
{
    public static function apply(): void
    {
        self::normalizeAppUrl();
        self::normalizeLogging();
        self::normalizeSession();
    }

    private static function normalizeAppUrl(): void
    {
        $url = config('app.url');

        if (! is_string($url) || $url === '') {
            return;
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = 'https://'.$url;
            config(['app.url' => $url]);
        }
    }

    private static function normalizeLogging(): void
    {
        if (app()->environment('production')) {
            config(['logging.default' => 'stderr']);
        }
    }

    private static function normalizeSession(): void
    {
        $domain = env('SESSION_DOMAIN');

        if ($domain === 'null' || $domain === '') {
            config(['session.domain' => null]);
        }

        if (app()->environment('production') && config('session.secure') === null) {
            config(['session.secure' => true]);
        }
    }
}
