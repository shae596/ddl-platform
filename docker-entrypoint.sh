#!/bin/sh
set -e

echo "==> DDL platform starting..."

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is missing. Set it in Railway → Variables."
    exit 1
fi

# Railway injecte DATABASE_URL quand Postgres est relié au service Web.
if [ -n "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
    export DB_URL="$DATABASE_URL"
fi

if [ -z "$DB_URL" ] && [ -z "$PGHOST" ]; then
    echo "ERROR: No database config found."
    echo "       Link PostgreSQL to this service (Connect) or set DB_URL / DATABASE_URL."
    exit 1
fi

# Les anciennes variables locales écrasent l'URL Railway si elles restent définies.
if [ -n "$DB_URL" ] || [ -n "$DATABASE_URL" ]; then
    unset DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD
fi

if [ -n "$DB_URL" ]; then
    echo "==> Database: using DB_URL / DATABASE_URL"
elif [ -n "$PGHOST" ]; then
    echo "==> Database: using PGHOST=$PGHOST"
fi

php artisan config:clear

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
