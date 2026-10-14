# ==============================================================================
# Stage 1: Build Frontend Assets (Vite)
# ==============================================================================
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci || npm install
COPY . .
RUN npm run build

# ==============================================================================
# Stage 2: Composer Dependencies (Vendor)
# ==============================================================================
FROM composer:2 AS vendor
WORKDIR /app
COPY composer*.json ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# ==============================================================================
# Stage 3: PHP 8.4-FPM Alpine Production Runtime
# ==============================================================================
FROM php:8.4-fpm-alpine AS runtime

# Install system dependencies & build tools
RUN apk add --no-cache \
    bash \
    curl \
    ffmpeg \
    freetype-dev \
    git \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    su-exec

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        dom \
        exif \
        fileinfo \
        gd \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        xml \
        zip

# Copy PHP and OPcache configurations
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini

# Set working directory
WORKDIR /var/www

# Copy application codebase
COPY . /var/www

# Copy vendor from Stage 2
COPY --from=vendor /app/vendor /var/www/vendor

# Copy compiled frontend assets from Stage 1
COPY --from=frontend /app/public/build /var/www/public/build

# Setup entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
