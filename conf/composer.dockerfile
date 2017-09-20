FROM composer/composer:php5

RUN docker-php-ext-install pdo pdo_mysql
