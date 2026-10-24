# ==============================================================================
# Travel Photo Journal — Minimalist Dockerfile
# Base: Official PHP 8.4 CLI on Debian Bookworm
# ==============================================================================

FROM php:8.4-cli-bookworm

# 1. Install required system libraries
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 2. Configure and install PHP extensions (PDO MySQL, GD for photos, Zip, Pcntl)
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo_mysql zip gd pcntl

# 3. Install latest Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# 4. Copy dependency definitions first for Docker layer caching
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

# 5. Copy application source
COPY . .

# 6. Generate optimized autoloader and set up storage directories & permissions
RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/sessions \
                storage/framework/views \
                storage/framework/cache \
                storage/logs \
                public/uploads/photos \
    && chmod -R 775 storage bootstrap/cache public/uploads

EXPOSE 8000

# 7. Start Laravel's built-in server (ensuring runtime storage permissions even with bind mount)
CMD ["sh", "-c", "mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs public/uploads/photos && chmod -R 775 storage bootstrap/cache public/uploads && exec php artisan serve --host=0.0.0.0 --port=8000"]
