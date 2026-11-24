
#!/bin/sh
set -eu

echo "==> Starting Laravel PHP-FPM container"

mkdir -p \
    /var/www/html/storage/app/public \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

# A named Docker volume may start with root ownership.
chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Fail early if required runtime configuration is missing.
if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY is missing."
    exit 1
fi

if [ ! -L /var/www/html/public/storage ]; then
    php /var/www/html/artisan storage:link --force
fi

# Keep the runtime environment authoritative. Do not bake
# production configuration or secrets into the image.
exec "$@"
