FROM php:8.1-fpm

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www

COPY . .

RUN composer install

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
