FROM php:7.3-fpm-alpine

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /opt

# Install dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    curl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install -j$(nproc) \
    mongodb \
    pdo \
    curl \
    json \
    opcache

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data ./src /opt/src/


# Expose port 9000 and start php-fpm server
EXPOSE 80
CMD ["php-fpm"]
