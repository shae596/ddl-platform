<?php

namespace App\Support;

use Illuminate\Database\ConfigurationUrlParser;

final class RailwayDatabaseConfig
{
    /** @var list<string> */
    private const URL_ENV_KEYS = [
        'DATABASE_PRIVATE_URL',
        'DATABASE_URL',
        'DB_URL',
    ];

    public static function apply(): void
    {
        $url = self::firstDatabaseUrl();

        if ($url !== null) {
            $parsed = (new ConfigurationUrlParser)->parseConfiguration([
                'driver' => 'pgsql',
                'url' => $url,
            ]);

            $host = $parsed['host'] ?? null;

            config([
                'database.default' => 'pgsql',
                'database.connections.pgsql' => array_merge(
                    config('database.connections.pgsql'),
                    $parsed,
                    [
                        'url' => $url,
                        'sslmode' => self::sslModeForHost(is_string($host) ? $host : null),
                    ],
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
            'database.connections.pgsql.sslmode' => self::sslModeForHost($host),
        ]);
    }

    public static function firstDatabaseUrl(): ?string
    {
        foreach (self::URL_ENV_KEYS as $key) {
            $value = self::nonEmpty(getenv($key));

            if ($value !== null && ! self::isUnresolvedReference($value)) {
                return $value;
            }
        }

        return null;
    }

    /** @return array<string, string|null> */
    public static function diagnosticSnapshot(): array
    {
        $snapshot = [];

        foreach ([...self::URL_ENV_KEYS, 'PGHOST', 'PGPORT', 'PGDATABASE', 'PGUSER', 'DB_CONNECTION'] as $key) {
            $raw = getenv($key);

            if ($raw === false || trim($raw) === '') {
                $snapshot[$key] = null;
                continue;
            }

            if (self::isSensitive($key)) {
                $snapshot[$key] = 'set('.strlen($raw).' chars)';
                continue;
            }

            if (self::isUnresolvedReference($raw)) {
                $snapshot[$key] = 'UNRESOLVED_REFERENCE';
                continue;
            }

            $snapshot[$key] = $raw;
        }

        $url = self::firstDatabaseUrl();

        if ($url !== null) {
            $parsed = (new ConfigurationUrlParser)->parseConfiguration([
                'driver' => 'pgsql',
                'url' => $url,
            ]);
            $snapshot['resolved_host'] = $parsed['host'] ?? null;
            $snapshot['resolved_database'] = $parsed['database'] ?? null;
        } else {
            $snapshot['resolved_host'] = null;
            $snapshot['resolved_database'] = null;
        }

        return $snapshot;
    }

    private static function sslModeForHost(?string $host): string
    {
        if ($host === null) {
            return 'prefer';
        }

        if (str_contains($host, 'railway.internal') || $host === '127.0.0.1' || $host === 'localhost') {
            return 'prefer';
        }

        return 'require';
    }

    private static function isUnresolvedReference(string $value): bool
    {
        return str_contains($value, '${{') || str_contains($value, '}}');
    }

    private static function isSensitive(string $key): bool
    {
        return in_array($key, ['DATABASE_PRIVATE_URL', 'DATABASE_URL', 'DB_URL', 'PGPASSWORD'], true);
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
