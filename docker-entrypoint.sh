#!/bin/sh
set -e

echo "==> DDL platform starting..."

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is missing. Set it in Railway → Variables."
    exit 1
fi

# Priorité Railway : URL privée > URL publique > DB_URL manuelle
if [ -n "$DATABASE_PRIVATE_URL" ]; then
    export DB_URL="$DATABASE_PRIVATE_URL"
elif [ -n "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
    export DB_URL="$DATABASE_URL"
fi

echo "==> Env check (présence uniquement) :"
for VAR in DATABASE_PRIVATE_URL DATABASE_URL DB_URL PGHOST PGPORT PGDATABASE PGUSER DB_CONNECTION; do
    if [ -n "$(eval echo \$$VAR)" ]; then
        echo "    $VAR=set"
    else
        echo "    $VAR=missing"
    fi
done

if [ -z "$DB_URL" ] && [ -z "$DATABASE_URL" ] && [ -z "$DATABASE_PRIVATE_URL" ] && [ -z "$PGHOST" ]; then
    echo "ERROR: Aucune variable base de données sur ce service."
    echo "       Sur ddl-platform → Variables, ajoutez :"
    echo "       DB_URL = \${{NomExactPostgres.DATABASE_URL}}"
    echo "       (remplacez NomExactPostgres par le nom affiché sur le service Postgres)"
    exit 1
fi

if [ -n "$DB_URL" ] || [ -n "$DATABASE_URL" ] || [ -n "$DATABASE_PRIVATE_URL" ]; then
    unset DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD
fi

php artisan config:clear

echo "==> Diagnostic base de données..."
php artisan railway:diagnose || {
    echo "ERROR: impossible de se connecter à PostgreSQL. Vérifiez DB_URL sur le service ddl-platform."
    exit 1
}

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
