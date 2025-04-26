FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite if needed
RUN a2enmod rewrite

# Copy your app
COPY ./public /var/www/html

# Optional: Set working directory
WORKDIR /var/www/html