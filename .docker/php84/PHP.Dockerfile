FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends vim curl debconf subversion git apt-transport-https apt-utils \
    build-essential locales acl mailutils wget nodejs zip unzip \
    gnupg \
    zlib1g-dev \
    libzip-dev \
    libicu-dev \
    sudo \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql zip intl

COPY .docker/php84/php.ini /usr/local/etc/php/conf.d/zz-f1tipp.ini

#RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
