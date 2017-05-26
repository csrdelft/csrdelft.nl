FROM php:5.6-apache

# update
RUN apt-get update \
 && DEBIAN_FRONTEND=noninteractive apt-get -y install sendmail \
 && rm -r /var/lib/apt/lists/*

# install php extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN pecl install xdebug && docker-php-ext-enable xdebug

# enable apache mods
RUN a2enmod rewrite

ENV BASE /var/www/csrdelft.nl

# copy config
COPY conf/dev/apache2.conf /etc/apache2/apache2.conf

# copy the source
COPY . /var/www/csrdelft.nl

# set permissions on DATA directories
RUN chown -R www-data ${BASE}/data && \
  chmod -R u+rw ${BASE}/data/ && \
  chmod -R u+rw ${BASE}/htdocs/wiki/data/
