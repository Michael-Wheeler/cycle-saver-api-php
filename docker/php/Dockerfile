FROM composer:1.9.3 AS composer
WORKDIR /opt

COPY composer.json /opt
COPY composer.lock /opt
RUN composer install --ignore-platform-reqs

FROM php:7.4-fpm-alpine

COPY --from=composer /opt/vendor /opt/src/vendor
#TODO Decide what we can leave out of copying to docker
COPY ./ /opt/

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

COPY --chown=www-data ./src /opt/src/

EXPOSE 80
WORKDIR /opt
RUN chmod +x bin/test
CMD ["php-fpm"]
