FROM composer:2.8 as composer

WORKDIR /installing
COPY ./ /installing
RUN composer install --no-dev --no-progress --no-autoloader && rm -rf vendor


FROM php:8.1-apache

LABEL org.opencontainers.image.source="https://github.com/wbstack/widar"

# For session storage
RUN pecl install redis && docker-php-ext-enable redis

COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/php.ini /usr/local/etc/php/conf.d/php.ini

COPY --from=composer /installing/public_html /var/www/html

ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]