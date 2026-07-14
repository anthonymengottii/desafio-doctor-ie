#!/bin/sh
set -e

# Garante um .env (as variaveis de DB/APP vem do ambiente do compose).
[ -f .env ] || cp .env.example .env

# Gera APP_KEY apenas se nao veio pelo ambiente.
if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force
fi

# Aguarda o Postgres aceitar conexoes.
echo "Aguardando o banco em ${DB_HOST}:${DB_PORT}..."
until php -r "new PDO('pgsql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
  sleep 2
done
echo "Banco disponivel."

# Migra e popula (seeder e idempotente).
php artisan migrate --force
php artisan db:seed --force

exec "$@"
