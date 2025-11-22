FROM php:8.2-apache

WORKDIR /var/www/html

# تثبيت المتطلبات الأساسية
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev zip \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install pdo pdo_mysql zip \
    && a2enmod rewrite

# نسخ المشروع
COPY . .

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# إنشاء المجلدات المطلوبة إذا لم تكن موجودة
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p bootstrap/cache

# تثبيت ال dependencies
RUN composer config --global audit.block-insecure false
RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN npm install && npm run build

# تكوين التطبيق
RUN php artisan config:cache && php artisan route:cache

# صلاحيات التخزين (بعد التأكد من وجود المجلدات)
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
