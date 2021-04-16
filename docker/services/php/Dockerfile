FROM php:7.4-fpm-alpine3.13

ENV LANG "C.UTF-8"
ENV LANGUAGE "C.UTF-8"
ENV LC_ALL "C.UTF-8"

# Install dependencies
RUN set -ex \
    && apk update \
    && apk add --no-cache \
        bash \
        git \
        icu-libs \
        nano

# User utils
RUN apk --no-cache add shadow

# Install PHP extensions
RUN set -ex \
    && apk add --no-cache --virtual .build-deps \
        autoconf \
        file \
        gcc \
        libc-dev \
        make \
        zlib-dev \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del --no-network .build-deps

# Composer
RUN curl -sS https://getcomposer.org/installer | \
    php -- --filename=composer  --version=1.10.17 --install-dir=/usr/local/bin && \
    php /usr/local/bin/composer clear-cache

RUN apk add --no-cache \
    openssh-client \
    su-exec

# Fix files permissions
RUN chown www-data:www-data /var/www
RUN groupmod -o -g 1000 www-data && usermod -o -u 1000 www-data
RUN groupadd php_conf
RUN usermod -a -G php_conf www-data
RUN chgrp -R php_conf "$PHP_INI_DIR/conf.d"
RUN chmod g+w -R "$PHP_INI_DIR/conf.d"

USER www-data

WORKDIR /var/www/mapper
