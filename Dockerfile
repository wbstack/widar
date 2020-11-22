FROM composer:1.10 as composer

WORKDIR /installing
COPY ./ /installing
RUN composer install --no-dev --no-progress --no-autoloader && rm -rf vendor


FROM php:7.2-apache

LABEL org.opencontainers.image.source="https://github.com/wbstack/widar"

# For session storage
RUN pecl install redis-4.0.1 && docker-php-ext-enable redis

COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/php.ini /usr/local/etc/php/conf.d/php.ini

COPY --from=composer /installing/public_html /usr/share/nginx/html

ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]