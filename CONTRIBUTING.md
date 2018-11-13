# Stappenplan tot contribueren aan de stek

## Stap 0

Zorg ervoor dat je een database dump krijgt van de PubCie, zonder deze dump is het erg ingewikkeld om de boel draaiende te krijgen. Zorg er ook voor dat je een plaetjes dump hebt.

## Stap 1
Maak een account aan op [GitHub](https://github.com).

## Stap 2
Installeer [git](https://git-scm.com). En vertel git met welke gegevens je aanpassingen wil maken, zorg dat het emailadres overeen komt met het account op GitHub. (Regels met een `$` er voor moeten uitgevoerd worden in powershell/bash)

```
$ git config --global user.name "John Doe"
$ git config --global user.email johndoe@example.com
```

### Stap 2.1
Ben je geen lid van de PubCie, [maak een fork van de stek](https://github.com/csrdelft/csrdelft.nl/fork)

## Stap 3
Download de stek op je computer, als je net een fork hebt gemaakt gebruik dan de url van je zelfgemaakte repository.

```
$ git clone git@github.com:csrdelft/csrdelft.nl
$ cd csrdelft.nl
$ git submodule init
$ git submodule update
```

## Stap 4: Installatie

Er zijn twee mogelijke manieren om te installeren, met Docker of met de hand. Als je actief gaat ontwikkelen aan de stek is het met de hand opzetten aan te raden.


### Met de hand

Installeer Apache2 met PHP en MySQL. Op Windows is er XAMPP, wat dit makkelijk maakt.

#### Apache2

Maak in je `hosts` (`/etc/hosts` of `C:\Windows\system32\drivers\etc\hosts`) bestand een verwijzing van `dev.csrdelft.nl` naar `localhost`.

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

#### PHP

Enable `mod_ldap` en .. in `php.ini`

#### MySQL

Maak een database `csrdelft` aan.

```
CREATE USER 'csrdelft'@'localhost' IDENTIFIED BY 'bl44t';
CREATE DATABASE `csrdelft` ;
GRANT ALL PRIVILEGES ON `csrdelft` . * TO 'csrdelft'@'localhost';
``` 

Hernoem `etc/mysql.ini.sample` naar `etc/mysql.ini` en voer de goede waarden in.

Plaats de export die je in stap 0 hebt gefixt in de database.

#### Dependencies

Gebruik [Composer](https://getcomposer.org/) om de dependencies te installeren door het volgende commando in de projectmap uit te voeren.

```bash
composer install
```

Installeer ImageMagick om het fotoalbum goed te laten werken. Controleer of de `IMAGEMAGICK` constante klopt.

Gebruik [yarn](https://yarnpkg.com) om javascript dependencies te installeren en om javascript te builden.

```bash
# Installeer dependencies
$ yarn

# Run build
$ yarn run dev

# Run build en blijf watchen
$ yarn run watch
# of
$ yarn run watch-poll
```

#### Plaetjes

Om jouw development versie goed te laten functioneren zijn er een aantal plaatjes nodig.
Probeer om ten minste deze te verkrijgen.

* /htdocs/plaetjes/famfamfam/
* /htdocs/plaetjes/geen-foto.jpg
* /htdocs/pleatjes/layout/

### Docker

Installeer [Docker](http://docker.com).

Kopieer de database dump naar de `data` map. Kopieer de plaetjes naar de `htdocs/plaetjes` map. Kopieer eventuele pasfoto's en fotoalbums naar `data/foto`

    # Start alles op, dit duurt de eerste keer ongeveer een kwartier
    $ docker-compose up

Dit zet alles klaar om de stek te runnen. Als dit klaar is kun je naar `http://localhost:8080` navigeren. Aanpassingen worden direct doorgevoerd.

Je kan met de database verbinden op `localhost:3307`, met PhpStorm, HeidiSQL, of wat anders. Met gebruikersnaam `csrdelft`, wachtwoord `bl44t` op database `csrdelft`.
    
Handige Docker commando's 

    # Voeg een php dependency toe
    $ docker-compose run composer require myVendor/package
    
    # Voer de migraties uit
    $ docker-compose run composer run-script migrate
    
    # Voeg een npm dependency toe
    $ docker-compose run yarn add myPackage
    
    # Als de javascript build om de een of andere reden is omgevallen
    $ docker-compose restart yarn

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

`component:forum`
`component:soccie`
...

## CmsPaginas

De volgende CMS pagina's zijn gehardcoded in de stek, zorg dat deze in de `cms_paginas` tabel aanwezig zijn.

* De lege CMS pagina, alle velden leeg
* `thuis`
* `accountaanvragen`
* `mobiel`
* `UitlegACL`
* `fotostoevoegen`
* `403`
