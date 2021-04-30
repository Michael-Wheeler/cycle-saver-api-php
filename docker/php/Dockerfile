# FROM composer:1.9.3 AS composer
# WORKDIR /opt
#
# COPY composer.* /opt/
#
# RUN composer install \
#     --no-interaction \
#     --no-autoloader \
#     --ignore-platform-reqs
#
# FROM composer as composer-autoload
# WORKDIR /opt
#
# COPY src/ /opt/src
# COPY tests/ /opt/tests
#
# RUN composer dump-autoload \
#     --no-interaction \
#     --classmap-authoritative

FROM php:7.4-fpm-alpine

# COPY --from=composer /opt/vendor /opt/vendor
# COPY --from=composer-autoload /opt/vendor/autoload.php /opt/vendor/
# COPY --from=composer-autoload /opt/vendor/composer /opt/vendor/composer

COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/nginx.conf /etc/nginx/nginx.conf
COPY ./src /opt/src
COPY ./tests /opt/tests
COPY ./public /opt/public
COPY ./bin /opt/bin
COPY ./vendor /opt/vendor

RUN apk add --update --no-cache \
    curl \
    vim \
    $PHPIZE_DEPS \
    libcurl \
    curl-dev
#     nginx

RUN docker-php-ext-install -j$(nproc) \
    pdo \
    curl \
#     opcache \
    json

RUN pecl install mongodb-1.7.2 \
    && docker-php-ext-enable mongodb

# RUN usermod -a -G root www-data

EXPOSE 80
WORKDIR /opt
RUN chmod +x bin/
CMD ["php-fpm"]
