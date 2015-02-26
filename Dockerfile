FROM php:5.6-apache

# update
RUN apt-get update
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install sendmail
RUN DEBIAN_FRONTEND=noninteractive apt-get -y install php5-mysql
RUN DEBIAN_FRONTEND=noninteractive apt-get \
  -o Dpkg::Options::="--force-confnew" \
  -y install libapache2-mod-php5

# cleanup
RUN rm -r /var/lib/apt/lists/*

# copy config
COPY conf/php.ini /usr/local/etc/php/
COPY conf/apache2.conf /etc/apache2/apache2.conf

# enable apache mods
RUN a2enmod rewrite

# copy the source
COPY . /var/www/csrdelft.nl
