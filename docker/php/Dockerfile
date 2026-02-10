FROM php:8.4-fpm-alpine

# Dependencias del sistema + extensiones PHP comunes para Laravel
RUN apk add --no-cache \
    bash curl git unzip icu-dev oniguruma-dev libzip-dev \
    mariadb-client \
    $PHPIZE_DEPS

RUN docker-php-ext-install \
    pdo pdo_mysql mbstring zip intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Usuario no-root (opcional pero recomendable)
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D -u 1000 laravel
USER laravel
