FROM php:8.0-apache
RUN docker-php-ext-install pdo pdo_mysql \
    && pecl install memcached \
    && docker-php-ext-enable memcached
COPY src/ /var/www/html/
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
