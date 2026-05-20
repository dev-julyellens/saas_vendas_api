# Imagem PHP 8.3 — requisito do projeto e do jwt-auth 2.9+ em produção.
# Extensões alinhadas ao stack: PostgreSQL, Redis, Sodium (JWT), OPcache.
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        opcache \
        pcntl \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis sodium \
    && apk del $PHPIZE_DEPS linux-headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN addgroup -g 1000 www && adduser -u 1000 -G www -s /bin/sh -D www
USER www

EXPOSE 9000
CMD ["php-fpm"]
