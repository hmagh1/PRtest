FROM php:8.0-apache

# 1. Installer les dépendances pour pdo_mysql et memcached
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
       libzip-dev \
       libmemcached-dev \
       zlib1g-dev \
       libsasl2-dev \
       pkg-config \
  && docker-php-ext-install pdo pdo_mysql \
  && pecl install memcached \
  && docker-php-ext-enable memcached \
  && rm -rf /var/lib/apt/lists/*

# 2. Copier le code source
COPY src/ /var/www/html/

# 3. Activer mod_rewrite et configurer le DocumentRoot
RUN a2enmod rewrite

# 4. Mettre à jour la variable d'environnement correctement
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# 5. Adapter la configuration d'Apache pour pointer vers /public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
