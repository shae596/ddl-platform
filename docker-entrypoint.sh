#!/bin/sh
set -e

echo "==> DDL platform starting..."

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is missing. Set it in Railway → Variables."
    exit 1
fi

if [ -z "$DB_URL" ] && [ -z "$DATABASE_URL" ]; then
    echo "ERROR: DB_URL (or DATABASE_URL) is missing. Link PostgreSQL in Railway → Variables → Add Reference."
    exit 1
fi

php artisan config:clear

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
