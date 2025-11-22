FROM php:8.2-apache

WORKDIR /var/www/html

# تثبيت المتطلبات الأساسية بما فيها libzip
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    zip \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

# تثبيت Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

# تثبيت إضافات PHP الأخرى
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

# نسخ المشروع
COPY . .

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت ال dependencies
RUN composer config --global audit.block-insecure false
RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN npm install
RUN npm run build

# تكوين التطبيق
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# صلاحيات التخزين
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
