FROM composer/composer:php7

RUN docker-php-ext-install pdo pdo_mysql
