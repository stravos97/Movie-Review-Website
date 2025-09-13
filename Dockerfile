# syntax=docker/dockerfile:1

FROM php:7.4-cli AS vendor
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev zlib1g-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY composer.json composer.lock ./
# Use Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

FROM php:7.4-apache

# Install system deps and PHP extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl libpng-dev libicu-dev libonig-dev libzip-dev unzip git $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_mysql intl \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && { \
         echo "; Xdebug defaults (disabled by default, enable via XDEBUG_MODE env in dev)"; \
         echo "xdebug.mode=off"; \
         echo "xdebug.start_with_request=no"; \
         echo "xdebug.discover_client_host=1"; \
         echo "xdebug.client_host=host.docker.internal"; \
         echo "xdebug.client_port=9003"; \
       } > /usr/local/etc/php/conf.d/xdebug.ini \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache for Symfony public dir
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Environment defaults (override at runtime). Do not bake secrets into the image.
ENV APP_ENV=prod

EXPOSE 80

# Healthcheck placeholder
HEALTHCHECK --interval=30s --timeout=3s CMD curl -fsS http://localhost/ || exit 1
