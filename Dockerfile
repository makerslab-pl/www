# Use PHP 8.1 Apache image
FROM php:8.1-apache-bullseye

# Install required extensions and curl for healthcheck
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    curl \
    unzip \
    libxml2-dev \
    && docker-php-ext-install pdo_sqlite dom \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Create data directory and set permissions
RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html/data && \
    chmod -R 777 /var/www/html/data

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
