FROM php:8.4-cli-alpine

# Install system dependencies & PHP extensions required by Laravel
RUN apk add --no-cache \
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

# Command to launch Laravel web server at runtime
CMD ["sh", "-c", "mkdir -p storage/framework/cache/data storage/framework/views storage/framework/sessions storage/logs && chmod -R 777 storage bootstrap/cache && php artisan config:clear && php artisan cache:clear && php artisan storage:link || true; php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
