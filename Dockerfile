FROM php:8.2-apache

RUN apt update && apt install -y zip unzip

# Copy files
WORKDIR /var/www/html
COPY . .

# Set APP_ENV to prod
RUN sed -i 's/APP_ENV=dev/APP_ENV=prod/g' .env

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --optimize-autoloader

# Apache stuff
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN a2enmod rewrite
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Create the cache and log directories and set permissions
RUN mkdir -p /var/www/html/var/cache/api && \
  mkdir -p /var/www/html/var/log && \
  chown -R www-data:www-data /var/www/html/var/cache/api && \
  chown -R www-data:www-data /var/www/html/var/log && \
  chmod -R 775 /var/www/html/var/cache/api && \
  chmod -R 775 /var/www/html/var/log

