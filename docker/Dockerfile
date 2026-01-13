FROM composer:lts as composer-dev
WORKDIR /app
COPY . .
########################################################################################################################

FROM composer:lts as composer-prod
WORKDIR /app
COPY . .
#RUN composer install --no-dev --no-interaction
########################################################################################################################

FROM php:8.5-apache as php-base
RUN docker-php-ext-install pdo pdo_mysql
########################################################################################################################

FROM php-base as development

RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && sed -i 's/Require all denied/Require all granted/' /etc/apache2/apache2.conf \
    && a2enmod rewrite \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN apt-get update && apt-get install -y git unzip
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY docker/php/conf.d/date.ini $PHP_INI_DIR/conf.d/date.ini
COPY docker/php/conf.d/xdebug.ini $PHP_INI_DIR/conf.d/xdebug.ini

COPY --from=composer-dev /usr/bin/composer /usr/bin/composer
COPY --from=composer-dev --chown=www-data:www-data /app /var/www/html/

WORKDIR /var/www/html

########################################################################################################################

FROM php-base as production

RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && sed -i 's/Require all denied/Require all granted/' /etc/apache2/apache2.conf \
    && a2enmod rewrite \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer-prod --chown=www-data:www-data /app /var/www/html/
COPY --from=composer-prod /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
USER www-data
