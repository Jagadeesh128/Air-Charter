# Use PHP with Apache
FROM php:8.2-apache

# Install PHP extensions (for MySQL and Symfony)
RUN apt-get update && apt-get install -y \
    git unzip zip curl libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql

# ✅ Enable Apache mod_rewrite (Symfony routing requires this)
RUN a2enmod rewrite

# ✅ Install Composer globally (official way)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# (Optional but recommended) Copy your Apache vhost config
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80
