FROM php:8.0-apache

# Installer les dépendances système
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       libzip-dev libmemcached-dev zlib1g-dev libsasl2-dev pkg-config unzip git \
  && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier composer.json, composer.lock et .env
COPY composer.json ./
COPY composer.lock ./
COPY .env ./

# Installer les dépendances PHP
RUN composer install --no-interaction --optimize-autoloader

# Installer les extensions PHP
RUN docker-php-ext-install pdo pdo_mysql zip \
  && pecl install memcached \
  && docker-php-ext-enable memcached

# Copier le code source, les tests et la config PHPUnit
COPY src/ ./src/
COPY tests/ ./tests/
COPY phpunit.xml ./

# Activer mod_rewrite et ajuster le DocumentRoot
RUN a2enmod rewrite \
  && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
