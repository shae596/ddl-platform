<?php

namespace App\Support;

use Illuminate\Database\ConfigurationUrlParser;

final class RailwayDatabaseConfig
{
    public static function apply(): void
    {
        $url = self::nonEmpty(getenv('DATABASE_URL'))
            ?: self::nonEmpty(getenv('DB_URL'));

        if ($url !== null) {
            $parsed = (new ConfigurationUrlParser)->parseConfiguration([
                'driver' => 'pgsql',
                'url' => $url,
            ]);

            config([
                'database.default' => 'pgsql',
                'database.connections.pgsql' => array_merge(
                    config('database.connections.pgsql'),
                    $parsed,
                    ['url' => $url],
                ),
            ]);

            return;
        }

        $host = self::nonEmpty(getenv('PGHOST'));

        if ($host === null) {
            return;
        }

        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.host' => $host,
            'database.connections.pgsql.port' => self::nonEmpty(getenv('PGPORT')) ?: '5432',
            'database.connections.pgsql.database' => self::nonEmpty(getenv('PGDATABASE')) ?: config('database.connections.pgsql.database'),
            'database.connections.pgsql.username' => self::nonEmpty(getenv('PGUSER')) ?: config('database.connections.pgsql.username'),
            'database.connections.pgsql.password' => getenv('PGPASSWORD') !== false ? getenv('PGPASSWORD') : config('database.connections.pgsql.password'),
        ]);
    }

    private static function nonEmpty(string|false|null $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
