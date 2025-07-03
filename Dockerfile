FROM php:8.0-apache

# System dependencies
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       libzip-dev libmemcached-dev zlib1g-dev libsasl2-dev pkg-config unzip git \
  && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock .env ./
RUN composer install --no-interaction --optimize-autoloader

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite zip \
  && pecl install memcached \
  && docker-php-ext-enable memcached \
  && rm -rf /var/lib/apt/lists/*

# Application code
COPY src/ ./src/
COPY tests/ ./tests/
COPY phpunit.xml ./

# Apache setup
RUN a2enmod rewrite \
  && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
EXPOSE 80
