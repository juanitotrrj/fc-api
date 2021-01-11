FROM php:8.0.0-fpm-alpine

RUN apk add supervisor git

WORKDIR /

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN git clone git@github.com:juanitotrrj/fc-api.git app

WORKDIR /app

# Change www-data user to match the host system UID and GID and chown www directory
RUN usermod --non-unique --uid 1000 www-data && \
    groupmod --non-unique --gid 1000 www-data && \
    chown -R www-data:www-data /app
