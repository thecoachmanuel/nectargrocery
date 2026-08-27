FROM php:8.4-cli-alpine

# Install system dependencies & PHP extensions required by Laravel
RUN apk add --no-cache \
    ca-certificates \
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
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.revalidate_freq=60" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy repository code
COPY . .

# Install PHP dependencies & build frontend assets
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs \
    && npm install \
    && npm run build \
    && chmod -R 777 storage bootstrap/cache

# Expose default HTTP port
EXPOSE 8080

# Command to launch PHP web server targeting public/ directory with production route & config caching
CMD ["sh", "-c", "mkdir -p storage/framework/cache/data storage/framework/views storage/framework/sessions storage/logs && chmod -R 777 storage bootstrap/cache && php artisan storage:link || true; php artisan config:cache && php artisan route:cache && php -S 0.0.0.0:${PORT:-8080} -t public"]
