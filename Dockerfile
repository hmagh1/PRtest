# 1. Base image
FROM php:8.0-apache

# 2. Install system dependencies
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libzip-dev \
      libmemcached-dev \
      zlib1g-dev \
      libsasl2-dev \
      pkg-config \
      unzip \
      git \
 && rm -rf /var/lib/apt/lists/*

# 3. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 4. Set working directory
WORKDIR /var/www/html

# 5. Copy Composer config and install PHP dependencies
COPY composer.json composer.lock .env ./
RUN composer install --no-interaction --optimize-autoloader

# 6. Install PHP extensions (PDO MySQL, PDO SQLite, zip) and Memcached
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite zip \
 && pecl install memcached \
 && docker-php-ext-enable memcached \
 && rm -rf /var/lib/apt/lists/*

# 7. Copy application source, tests, and PHPUnit config
COPY src/ ./src/
COPY tests/ ./tests/
COPY phpunit.xml ./

# 8. Enable Apache rewrite module and adjust DocumentRoot to /public
RUN a2enmod rewrite \
 && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

# 9. Expose port and set Apache env
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
EXPOSE 80
