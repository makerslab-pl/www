# Use PHP 8.4 Apache image
FROM php:8.4-apache-bookworm

# Install required extensions and curl for healthcheck
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    curl \
    unzip \
    libxml2-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo_sqlite dom xml xmlwriter mbstring tokenizer \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Create data directory and set permissions
RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html/data && \
    chmod -R 777 /var/www/html/data

# Configure PHP settings
RUN echo "memory_limit = \${PHP_MEMORY_LIMIT:-256M}" > /usr/local/etc/php/conf.d/docker-php-settings.ini && \
    echo "upload_max_filesize = \${PHP_UPLOAD_MAX_FILESIZE:-64M}" >> /usr/local/etc/php/conf.d/docker-php-settings.ini && \
    echo "post_max_size = \${PHP_POST_MAX_SIZE:-64M}" >> /usr/local/etc/php/conf.d/docker-php-settings.ini && \
    echo "max_execution_time = \${PHP_MAX_EXECUTION_TIME:-300}" >> /usr/local/etc/php/conf.d/docker-php-settings.ini

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
