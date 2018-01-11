# Contribueren aan de stek

## Installatie
Hieronder is uitleg te vinden voor het installeren van alle componenten voor het lokaal gebruiken van de stek.

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

Installeer ImageMagick om het fotoalbum goed te laten werken. Controleer of de `IMAGEMAGICK` constante klopt.

### Plaetjes

Om jouw development versie goed te laten functioneren zijn er een aantal plaatjes nodig.
Probeer om ten minste deze te verkrijgen.

* /htdocs/plaetjes/famfamfam/
* /htdocs/plaetjes/pasfoto/geen-foto.jpg
* /htdocs/pleatjes/layout/

## CmsPaginas

De volgende CMS pagina's zijn gehardcoded in de stek, zorg dat deze in de `cms_paginas` tabel aanwezig zijn.

* De lege CMS pagina, alle velden leeg
* `thuis`
* `accountaanvragen`
* `mobiel`
* `UitlegACL`
* `fotostoevoegen`
* `403`

### Docker

Op Linux en mac moet dit makkelijk draaien.
Op andere platforms kun je docker beter niet overwegen.

    # Run the stek and database
    docker-compose up stek

    # initialize the database (only need to do this once)
    # make sure you have the dump.sql in the root of your repo
    docker run -ti --rm --link <reponame>_stekdb_1:db -v `pwd`:/mnt mariadb bash -c 'exec mysql  -h"$DB_PORT_3306_TCP_ADDR" -u root -p csrdelft < /mnt/dump.sql'
    
    # install composer dependencies
    docker-compose run --rm composer install
    
    # add a dependency
    docker-compose run --rm composer require myVendor/package

## Development

### Branches & PR
We hebben enkele branches. Niet heel bijzonder.

- `master` -  live stek, niet stukmaken! Taart als dat wel gebeurd.
- `#issue - naam`: graag issue nummer (van github) vermelden indien mogelijk.
- `naam`: overig

We werken met PRs voor de meeste gevallen. Probeer je code door ten minste 1 persoon te laten reviewen.
Diegene mag hem mergen en zal er vervolgens voor zorgen dat de live stek wordt geupdate.


### Issues
#### Type
Spreekt redelijk voorzich. De kleur is een pastelkleur. De specifieke kleur per label is willekeurig.

`type:enhancement` - Verbetering voor de huidige code base / architectuur. Dit kan bijvoorbeeld een performance verbetering zijn maar ook een refactor.
`type:design` - Puur gericht op uiterlijk.
`type:feature` - Nieuwe toevoeging ten opzichte van huidige code base.
`type:bug`
`type:security`
`type:task`
`type:question`

#### Prioriteit
Het inschatten van dit label kan lastig zijn. Hieronder enkele richtlijnen. De kleuren voor deze labels zijn rood, oranje en geel.

`prio:high` - Moet veranderd worden om de stek draaiende te kunnen houden. Tevens problemen die het gebruik van de stek regelmatig verstoren.
`prio:normal` - Zaken die van waarde zijn maar de huidige stek niet ontregelen als ze niet gefixt worden. Denk hierbij aan nieuwe refactors.
`prio:low` - Veelal issues die vooralsnog genegeerd kunnen worden. Bevatten vaak mooie ideeen, maar daar is nu geen tijd voor / behoefte aan.

#### Component
Het component label is altijd zwart en beschrijft het onderdeel van de stek waar dit over gaat. Deze wordt, in tegenstelling tot de vorige 2, niet consistent gebruikt. Dit zou in een later stadium nog gedaan kunnen worden. Mogelijk zou dit ook met milestones kunnen.

`compnent:forum`
`component:soccie`
...
