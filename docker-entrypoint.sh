#!/bin/sh
set -e

echo "==> DDL platform starting..."

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is missing. Set it in Railway → Variables."
    exit 1
fi

# Corrige APP_URL sans https:// (cause fréquente d'erreur 500)
if [ -n "$APP_URL" ] && ! echo "$APP_URL" | grep -q '^https\?://'; then
    export APP_URL="https://$APP_URL"
    echo "==> APP_URL normalisée avec https://"
fi

export APP_ENV=production
export LOG_CHANNEL=stderr

# Priorité Railway : URL privée > URL publique > DB_URL manuelle
if [ -n "$DATABASE_PRIVATE_URL" ]; then
    export DB_URL="$DATABASE_PRIVATE_URL"
elif [ -n "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
    export DB_URL="$DATABASE_URL"
fi

echo "==> Env check (présence uniquement) :"
for VAR in APP_URL DATABASE_PRIVATE_URL DATABASE_URL DB_URL PGHOST DB_CONNECTION LOG_CHANNEL; do
    if [ -n "$(eval echo \$$VAR)" ]; then
        echo "    $VAR=set"
    else
        echo "    $VAR=missing"
    fi
done

if [ -z "$DB_URL" ] && [ -z "$DATABASE_URL" ] && [ -z "$DATABASE_PRIVATE_URL" ] && [ -z "$PGHOST" ]; then
    echo "ERROR: Aucune variable base de données sur ce service."
    exit 1
fi

if [ -n "$DB_URL" ] || [ -n "$DATABASE_URL" ] || [ -n "$DATABASE_PRIVATE_URL" ]; then
    unset DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD
fi

# Nettoie SESSION_DOMAIN=null (texte) qui casse les cookies
if [ "$SESSION_DOMAIN" = "null" ] || [ "$SESSION_DOMAIN" = "" ]; then
    unset SESSION_DOMAIN
fi

mkdir -p storage/app/private storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

php artisan config:clear

echo "==> Diagnostic base de données..."
php artisan railway:diagnose || {
    echo "ERROR: impossible de se connecter à PostgreSQL."
    exit 1
}

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding users and parameters..."
php artisan db:seed --force

echo "==> Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
