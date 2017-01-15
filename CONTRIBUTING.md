# Contribueren aan de stek

## Installatie

### Apache2

Installeer een stack met Apache2 en MySQL (gebruik bijv. WampServer)

Maak in je `hosts` bestand een verwijzing van `dev.csrdelft.nl` naar `localhost`.

De volgende configuratie werkt goed voor Apache2, let op de `php_value include_path ...`.

```
<VirtualHost dev.csrdelft.nl:80>
    DocumentRoot "<repo root>\htdocs"
    ServerName dev.csrdelft.nl
    ServerAlias dev.csrdelft.nl
    ErrorLog "logs/dev.csrdelft.nl-error.log"
    #tell php to look in the lib-dir
    php_value include_path "<repo root>\lib"
    <Directory "<repo root>\htdocs">
        AllowOverride All
        Order Allow,Deny
        Allow from all
        Require all granted
    </Directory>
</VirtualHost>
```

### PHP

Enable `mod_ldap` en .. in `php.ini`

### MySQL

Maak een database `csrdelft` aan.

```SQL
CREATE USER 'csrdelft'@'localhost' IDENTIFIED BY 'wachtwoord';
CREATE DATABASE `csrdelft` ;
GRANT ALL PRIVILEGES ON `csrdelft` . * TO 'csrdelft'@'localhost';
``` 

Hernoem `etc/mysql.ini.sample` naar `etc/mysql.ini` en voer de goede waarden in.

Fix een export van de database en plaats deze in je lokale database.
Hiervoor kun je de fixtures gebruiken in de root folder. (user=x404, pass=civitasstekdebugaccount404)

### Dependencies

Gebruik [Composer](https://getcomposer.org/) om de dependencies te installeren door het volgende commando in de projectmap uit te voeren.

```bash
composer install
```

### Plaetjes

Om jouw development versie goed te laten functioneren zijn er een aantal plaatjes nodig.
Probeer om ten minste deze te verkrijgen.

* /htdocs/plaetjes/famfamfam/
* /htdocs/plaetjes/pasfoto/geen-foto.jpg
* /htdocs/pleatjes/layout/
* /htdocs/pleatjes/layout2/

### Docker

Op Linux moet dit makkelijk draaien.
Op andere platforms moet je gebruik maken van boot2docker.

    # Run the stek and database
    docker-compose up stek

    # initialize the database (only need to do this once)
    # make sure you have the dump.sql in the root of your repo
    docker run -ti --rm --link <reponame>_stekdb_1:db -v `pwd`:/mnt mariadb bash -c 'exec mysql  -h"$DB_PORT_3306_TCP_ADDR" -u root -p csrdelft < /mnt/dump.sql'

Plaetjes zitten niet standaard in deze repo. Maar als je ze in `htdocs/plaetjes` zet zal docker ze gebruiken.
