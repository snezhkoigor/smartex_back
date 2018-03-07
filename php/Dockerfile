#FROM php:7.2-fpm
FROM php:7.1-fpm
#FROM php:7.0-fpm

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_CACHE_DIR /composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" &&\
    php composer-setup.php --install-dir=/usr/bin --filename=composer &&\
    rm -f composer-setup.php

# Install extentions. See https://hub.docker.com/_/php/

# - PHP Core Extensions
RUN apt-get update && apt-get install -y \
       libfreetype6-dev \
       libjpeg62-turbo-dev \
       libmcrypt-dev \
       libpng-dev \
    && docker-php-ext-install zip \
    && docker-php-ext-install -j$(nproc) iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# - PECL extensions
RUN pecl install redis-3.1.6 \
    && pecl install xdebug-2.6.0 \
    && docker-php-ext-enable redis xdebug

#git
RUN apt-get update && apt-get install -y git