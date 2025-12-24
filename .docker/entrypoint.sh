#!/bin/sh

set -e

# Laravel permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Generate app key if missing
php artisan key:generate --force || true

# Cache config for production
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (optional – remove if you don’t want auto-migrate)
php artisan migrate --force || true

# Start PHP-FPM and Nginx
php-fpm -D
nginx -g "daemon off;"
