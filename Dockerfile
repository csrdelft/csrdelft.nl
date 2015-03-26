FROM php:5.6-apache

ENV BASE /var/www/csrdelft.nl

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
COPY conf/dev/php.ini /usr/local/etc/php/
COPY conf/dev/apache2.conf /etc/apache2/apache2.conf
COPY conf/dev/defines.include.php /var/www/csrdelft.nl/lib/
COPY conf/dev/mysql.ini /var/www/csrdelft.nl/etc/

# enable apache mods
RUN a2enmod rewrite

# copy the source
COPY . /var/www/csrdelft.nl

# set permissions on DATA directories
RUN mkdir ${BASE}/data/ && \
  chown -R www-data ${BASE}/data && \
  chmod -R u+rw ${BASE}/data/ && \
  chmod -R u+rw ${BASE}/htdocs/wiki/data/
