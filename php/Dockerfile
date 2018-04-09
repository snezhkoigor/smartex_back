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

# Install Postgre PDO
RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# - PECL extensions
RUN pecl install redis-3.1.6 \
    && pecl install xdebug-2.6.0 \
    && docker-php-ext-enable redis xdebug

RUN echo xdebug.remote_enable=1 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo xdebug.remote_port=9000 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo xdebug.remote_autostart=1 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo xdebug.remote_connect_back=0 >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo xdebug.idekey=PHPSTORM >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo xdebug.remote_host=docker.for.mac.localhost >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

#git
RUN apt-get update && apt-get install -y git