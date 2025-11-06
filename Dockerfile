FROM ubuntu:22.04

# تثبيت PHP 8.2
RUN apt-get update && apt-get install -y \
    software-properties-common \
    && add-apt-repository ppa:ondrej/php -y \
    && apt-get update \
    && apt-get install -y \
    php8.2 \
    php8.2-curl \
    php8.2-xml \
    php8.2-mbstring \
    php8.2-zip \
    php8.2-sqlite3 \
    php8.2-mysql \
    composer \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# إنشاء مجلد العمل
WORKDIR /app

# نسخ ملفات المشروع
COPY . .

# تثبيت ال dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs
RUN php artisan key:generate

# التعريض للمنفذ
EXPOSE $PORT

# تشغيل التطبيق
CMD php artisan serve --host=0.0.0.0 --port=$PORT
