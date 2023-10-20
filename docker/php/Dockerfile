# Offical Docker Image for PHP
# https://hub.docker.com/_/php
FROM php:8.2-fpm

# Set Current Directory
WORKDIR /var/www/vhost/

# Install System Dependencies
RUN apt-get clean && apt-get update --fix-missing && apt-get upgrade --yes && apt-get install --yes \
    git \
    libcurl4-openssl-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libssl-dev \
    libxml2-dev \
    libzip-dev \
    supervisor \
    unzip \
    wget \
    zip

RUN docker-php-ext-configure gd --with-jpeg=/usr/include --with-webp

# PHP Extensions
# (ctype, fileinfo, json, mbstring, openssl, tokenizer and xml are already enabled)
# https://github.com/mlocati/docker-php-extension-installer
RUN docker-php-ext-install \
    bcmath \
    gd \
    intl \
    opcache \
    pdo_mysql \
    zip \
    pcntl
# PHP Config
COPY ./config/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./config/php.ini /usr/local/etc/php/conf.d/php.ini

# Install Composer from Official Docker Image
# https://hub.docker.com/_/composer
COPY --from=composer:2.5.4 /usr/bin/composer /usr/bin/composer
COPY ./worker /etc/supervisor/conf.d/
