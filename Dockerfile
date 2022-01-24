FROM composer@sha256:d374b2e1f715621e9d9929575d6b35b11cf4a6dc237d4a08f2e6d1611f534675 as composer
# composer is pinned at a PHP 7 version

WORKDIR /installing
COPY ./ /installing
RUN composer install --no-dev --no-progress --no-autoloader && rm -rf vendor


FROM php:8.1.2-apache

LABEL org.opencontainers.image.source="https://github.com/wbstack/widar"

# For session storage
RUN pecl install redis-4.0.1 && docker-php-ext-enable redis

COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/php.ini /usr/local/etc/php/conf.d/php.ini

COPY --from=composer /installing/public_html /var/www/html

ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]