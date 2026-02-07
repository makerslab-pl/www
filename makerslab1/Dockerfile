# MakersLab - Dockerfile
# PHP 8.2 + Apache + SQLite

FROM php:8.2-apache

# Metadata
LABEL maintainer="MakersLab <kontakt@makerslab.pl>"
LABEL description="MakersLab - Warsztaty robotyki dla dzieci"
LABEL version="1.0"

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite zip

# Configure PHP settings from environment variables
RUN echo "memory_limit = ${PHP_MEMORY_LIMIT:-256M}" > /usr/local/etc/php/conf.d/docker-php-settings.ini && \
    echo "upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE:-64M}" >> /usr/local/etc/php/conf.d/docker-php-settings.ini && \
    echo "post_max_size = ${PHP_POST_MAX_SIZE:-64M}" >> /usr/local/etc/php/conf.d/docker-php-settings.ini && \
    echo "max_execution_time = ${PHP_MAX_EXECUTION_TIME:-300}" >> /usr/local/etc/php/conf.d/docker-php-settings.ini

# Enable Apache modules
RUN a2enmod rewrite headers expires deflate

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Configure Apache virtual host
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Create data directory with proper permissions
RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Copy application files
COPY --chown=www-data:www-data . /var/www/html/

# Set permissions for data directory
RUN chmod -R 777 /var/www/html/data

# Install Composer (for tests)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
