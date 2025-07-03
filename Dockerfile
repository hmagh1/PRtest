FROM php:8.0-apache

# 1. Installer les dépendances système
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       libzip-dev \
       libmemcached-dev \
       zlib1g-dev \
       libsasl2-dev \
       pkg-config \
  && rm -rf /var/lib/apt/lists/*

# 2. Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3. Définir le répertoire de travail
WORKDIR /var/www/html

# 4. Copier seulement composer.json pour installer les dépendances
COPY composer.json ./
RUN composer install --no-interaction --optimize-autoloader

# 5. Installer les extensions PHP
RUN docker-php-ext-install pdo pdo_mysql \
  && pecl install memcached \
  && docker-php-ext-enable memcached

# 6. Copier le code source
COPY src/ ./

# 7. Activer mod_rewrite et configurer DocumentRoot
RUN a2enmod rewrite \
  && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
