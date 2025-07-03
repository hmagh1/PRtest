FROM php:8.0-apache

# 1. Installer les dépendances système nécessaires
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

# 2. Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3. Définir le répertoire de travail
WORKDIR /var/www/html

# 4. Copier les fichiers de configuration et d'environnement
COPY composer.json composer.lock .env ./

# 5. Installer les dépendances PHP via Composer
RUN composer install --no-interaction --optimize-autoloader

# 6. Installer les extensions PHP (PDO, MySQLi, zip) et Memcached
RUN docker-php-ext-install pdo pdo_mysql mysqli zip \
  && pecl install memcached \
  && docker-php-ext-enable memcached

# 7. Copier le code source, les tests et la config PHPUnit
COPY src/ ./src/
COPY tests/ ./tests/
COPY phpunit.xml ./

# 8. Activer mod_rewrite et ajuster le DocumentRoot pour /public
RUN a2enmod rewrite \
  && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

# 9. Définir la variable d'environnement pour Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
