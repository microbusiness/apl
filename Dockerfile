FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
        acl \
        fcgi \
        icu-libs \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
    && docker-php-ext-install -j$(nproc) \
        intl \
        opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .build-deps \
    && rm -rf /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ---------- deps stage ----------
FROM base AS deps

COPY composer.json composer.lock symfony.lock ./

RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

# ---------- build stage ----------
FROM base AS build

COPY --from=deps /app/vendor ./vendor
COPY . .

RUN composer dump-env prod \
    && composer run-script --no-dev post-install-cmd \
    && php bin/console cache:warmup --env=prod

# ---------- production image ----------
FROM php:8.4-fpm-alpine AS production

RUN apk add --no-cache \
        acl \
        fcgi \
        icu-libs \
        nginx \
        supervisor \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
    && docker-php-ext-install -j$(nproc) \
        intl \
        opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apk del .build-deps \
    && rm -rf /tmp/pear

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini     /usr/local/etc/php/conf.d/app.ini
COPY docker/php/www.conf    /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf   /etc/supervisord.conf

WORKDIR /app

COPY --from=build /app ./

RUN chown -R www-data:www-data var \
    && mkdir -p /run/nginx

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=3s --retries=3 \
    CMD wget -qO- http://127.0.0.1/ping || exit 1

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
