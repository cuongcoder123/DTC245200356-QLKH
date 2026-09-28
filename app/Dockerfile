FROM php:8.2-apache

# Cai dat cac extension can thiet cho MySQL va Apache
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql mysqli zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Cau hinh thu muc chua log he thong
RUN mkdir -p /var/log/app && chmod 777 /var/log/app

# Copy custom php.ini
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Thiet lap Security Hardening: Tao non-root user (appuser UID 1001)
RUN groupadd -g 1001 appgroup && \
    useradd -u 1001 -g appgroup -s /bin/bash appuser

# Copy ma nguon vao thu muc web root
COPY src/ /var/www/html/

# Phan quyen thu muc web
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chown -R www-data:www-data /var/log/app

EXPOSE 80

CMD ["apache2-foreground"]
