
# --------------------------------------------------
# 1. Build frontend assets
# --------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build


# --------------------------------------------------
# 2. Laravel PHP-FPM application
# --------------------------------------------------
FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache \
    ffmpeg \
    libpq \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    libxml2 \
    oniguruma \
    bash \
    curl \
    supervisor \
    postgresql-dev \
    mariadb-connector-c-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    linux-headers \
    autoconf \
    gcc \
    g++ \
    make \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pdo_mysql \
        mysqli \
        pdo \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del --no-cache autoconf gcc g++ make linux-headers \
    && rm -rf /var/cache/apk/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies from the lockfile first.
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# Copy the application source.
COPY . .

# Replace the source tree's public/build with built Vite assets.
COPY --from=frontend /app/public/build ./public/build

# Create Laravel's public storage symlink at build time so
# it is also present in the separate Nginx image.

# Prepare Laravel writable directories and public storage link.
RUN set -eux; \
    mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache; \
    if [ -L public/storage ]; then \
        echo "Storage symlink already exists"; \
    elif [ -e public/storage ]; then \
        echo "ERROR: public/storage exists and is not a symlink"; \
        ls -ld public/storage; \
        exit 1; \
    else \
        ln -s ../storage/app/public public/storage; \
    fi; \
    rm -f bootstrap/cache/packages.php \
          bootstrap/cache/services.php; \
    composer dump-autoload \
        --no-dev \
        --optimize \
        --no-scripts; \
    php artisan package:discover --ansi; \
    chown -R www-data:www-data storage bootstrap/cache


# Production OPcache settings.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf '%s\n' \
        'opcache.memory_consumption=128' \
        'opcache.interned_strings_buffer=16' \
        'opcache.max_accelerated_files=20000' \
        'opcache.validate_timestamps=0' \
        > "$PHP_INI_DIR/conf.d/opcache-production.ini"


# Update php upload limit and post max size to 100M
RUN printf '%s\n' \
        'post_max_size=32M' \
        'upload_max_filesize=32M' \
        > "$PHP_INI_DIR/conf.d/uploads.ini"

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]


# --------------------------------------------------
# 3. Separate Nginx frontend
# --------------------------------------------------
FROM nginx:stable-alpine AS nginx

RUN rm -f /etc/nginx/conf.d/default.conf

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY --from=app /var/www/html/public /var/www/html/public

EXPOSE 80