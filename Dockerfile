FROM php:8.2-apache

# Install system dependencies, PDO MySQL, and other PHP extensions Symfony/MySQL apps need
RUN docker-php-ext-install pdo pdo_mysql

# (Optional) Install other extensions you might use
# RUN docker-php-ext-install intl zip opcache

# Enable Apache mod_rewrite (often needed for Symfony)
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy custom Apache configuration (host path must exist BEFORE build)
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf
