FROM php:8.2-apache

# Install required extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite headers

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Copy application files
COPY ./public /var/www/html/

# Set working directory
WORKDIR /var/www/html