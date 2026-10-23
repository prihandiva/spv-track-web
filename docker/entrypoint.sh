#!/bin/bash
set -e

PORT=${PORT:-10000}

# Update Apache port dynamically to match Render's $PORT
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Ensure storage symlink exists
php artisan storage:link || true

# Fix permissions for storage and cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Run migrations if database is connected
php artisan migrate --force || true

# Clear and cache configurations
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "==> SPV-Track is running on port ${PORT}"
exec apache2-foreground
