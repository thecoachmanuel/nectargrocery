FROM php:8.4-fpm-alpine

# Install system dependencies, Nginx & Supervisor
RUN apk add --no-cache \
    ca-certificates \
    nginx \
    supervisor \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    nodejs \
    npm \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip bcmath intl mbstring exif opcache

# Configure PHP OPcache for maximum production performance
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.revalidate_freq=60" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application codebase
COPY . .

# Copy Nginx & Supervisor configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install PHP dependencies & build frontend assets
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs \
    && npm install \
    && npm run build \
    && chmod -R 777 storage bootstrap/cache

# Expose default HTTP port
EXPOSE 8080

# Command to launch Nginx & PHP-FPM concurrently via Supervisor
CMD ["sh", "-c", "sed -i \"s/listen 8080;/listen ${PORT:-8080};/g\" /etc/nginx/nginx.conf && mkdir -p storage/framework/cache/data storage/framework/views storage/framework/sessions storage/logs && chmod -R 777 storage bootstrap/cache && php artisan storage:link || true; php artisan config:cache && php artisan route:cache && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]
