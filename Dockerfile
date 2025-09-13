# syntax=docker/dockerfile:1

FROM php:7.4-cli AS vendor
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt/lists,sharing=locked \
    apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev zlib1g-dev \
    && docker-php-ext-install zip
WORKDIR /app
COPY composer.json composer.lock ./
# Use Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
ENV COMPOSER_NO_INTERACTION=1 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_SCRIPTS=1
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --prefer-dist --no-progress --optimize-autoloader --no-scripts

FROM php:7.4-apache

# Optional Xdebug (disabled by default)
ARG WITH_XDEBUG=false

# Install system deps and PHP extensions
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt/lists,sharing=locked \
    set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends curl libpng-dev libicu-dev libonig-dev libzip-dev unzip git; \
    docker-php-ext-install pdo pdo_mysql intl; \
    if [ "$WITH_XDEBUG" = "true" ]; then \
      apt-get install -y --no-install-recommends $PHPIZE_DEPS; \
      pecl install xdebug; \
      docker-php-ext-enable xdebug; \
      { \
         echo "; Xdebug defaults (disabled by default, enable via XDEBUG_MODE env in dev)"; \
         echo "xdebug.mode=off"; \
         echo "xdebug.start_with_request=no"; \
         echo "xdebug.discover_client_host=1"; \
         echo "xdebug.client_host=host.docker.internal"; \
         echo "xdebug.client_port=9003"; \
       } > /usr/local/etc/php/conf.d/xdebug.ini; \
    fi; \
    a2enmod rewrite

# Configure Apache for Symfony public dir
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Environment defaults (override at runtime). Do not bake secrets into the image.
ENV APP_ENV=prod

# Ensure writable cache/log directories for Symfony
RUN set -eux; \
    mkdir -p var/cache var/log; \
    chown -R www-data:www-data var; \
    chmod -R 775 var

# Warm up Symfony cache in production mode (non-fatal if not fully configured at build time)
RUN php bin/console cache:warmup --env=prod || true

EXPOSE 80

# Healthcheck placeholder
HEALTHCHECK --interval=30s --timeout=3s CMD curl -fsS http://localhost/ || exit 1
