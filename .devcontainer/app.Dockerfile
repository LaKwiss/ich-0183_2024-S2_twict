FROM mcr.microsoft.com/devcontainers/php:dev-8.3-apache-bullseye

COPY ./config/apache/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite