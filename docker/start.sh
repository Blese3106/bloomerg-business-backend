#!/bin/bash
set -e

echo "Starting Laravel app..."

# Attendre DB (plus simple et fiable)
echo "Checking database connection..."
sleep 5

# Migrations (safe)
php /var/www/artisan migrate --force

# ⚠️ PAS de key:generate en prod
# APP_KEY doit être dans Railway/Render env

# Cache Laravel
php /var/www/artisan config:cache
php /var/www/artisan route:cache
php /var/www/artisan view:cache

echo "Laravel ready!"

# Lancer supervisor
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf