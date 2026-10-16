FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends unzip libzip-dev \
    && docker-php-ext-install pdo_mysql bcmath \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/marketplace-entrypoint
RUN chmod +x /usr/local/bin/marketplace-entrypoint

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/marketplace-entrypoint"]