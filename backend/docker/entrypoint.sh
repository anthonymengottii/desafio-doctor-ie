#!/bin/sh
set -e

# Configuracao vem 100% do ambiente (docker-compose). Nao criamos .env para
# evitar que valores conflitantes (ex.: DB_HOST local) vazem em runtime.

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
