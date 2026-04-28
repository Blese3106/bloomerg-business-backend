#!/bin/bash
set -e

echo "Starting Laravel app..."

# Attendre la base de données (plus propre que sleep)
until php /var/www/artisan migrate:status > /dev/null 2>&1; do
  echo "Waiting for database connection..."
  sleep 2
done

echo "Database is ready!"

# Générer APP_KEY si absent
php /var/www/artisan key:generate --force || true

# Migrations
php /var/www/artisan migrate --force

# Seed seulement si tu es sûr
# php /var/www/artisan db:seed --force

# Cache Laravel
php /var/www/artisan config:cache
php /var/www/artisan route:cache
php /var/www/artisan view:cache

# Lancer supervisor
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf