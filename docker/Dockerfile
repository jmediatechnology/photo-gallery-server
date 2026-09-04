FROM composer:lts as composer-dev
WORKDIR /app
COPY . .

########################################################################################################################

FROM php:8.5-apache as php-base
RUN docker-php-ext-install pdo pdo_mysql

COPY docker/php/conf.d/date.ini $PHP_INI_DIR/conf.d/date.ini
COPY docker/php/conf.d/uploads.ini $PHP_INI_DIR/conf.d/uploads.ini

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

COPY docker/php/conf.d/xdebug.ini $PHP_INI_DIR/conf.d/xdebug.ini

COPY --from=composer-dev /usr/bin/composer /usr/bin/composer
COPY --from=composer-dev --chown=www-data:www-data /app /var/www/html/

WORKDIR /var/www/html

########################################################################################################################

FROM php-base as production

RUN apt-get update && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && sed -i 's/Require all denied/Require all granted/' /etc/apache2/apache2.conf \
    && a2enmod rewrite \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/variables_order = "GPCS"/variables_order = "EGPCS"/' "$PHP_INI_DIR/php.ini"

ENV APP_ENV=prod
ENV APP_DEBUG=0

COPY --from=composer:lts /usr/bin/composer /usr/bin/composer
COPY --chown=www-data:www-data . /var/www/html/

WORKDIR /var/www/html
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts \
    && mkdir -p var/cache var/log \
    && chown -R www-data:www-data vendor var

USER www-data
