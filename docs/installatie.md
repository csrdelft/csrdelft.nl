# Installatie van de stek

Volg dit stappenplan om de stek op je eigen computer te installeren.

## Stap 0
Zorg ervoor dat je een database dump krijgt van de PubCie, zonder deze dump is het erg ingewikkeld om de boel draaiende te krijgen. Zorg er ook voor dat je een plaetjes dump hebt.
<br><br>

## Stap 1
Maak een account aan op [GitHub](https://github.com).
<br><br>

## Stap 2
Installeer [git](https://git-scm.com). En vertel git met welke gegevens je aanpassingen wil maken, zorg dat het emailadres overeen komt met het account op GitHub. (Regels met een `$` er voor moeten uitgevoerd worden in powershell/bash)

```
$ git config --global user.name "John Doe"
$ git config --global user.email johndoe@example.com
```

### Stap 2.1
Ben je geen lid van de PubCie, [maak een fork van de stek](https://github.com/csrdelft/csrdelft.nl/fork)
<br><br>

## Stap 3
Download de stek op je computer, als je net een fork hebt gemaakt gebruik dan de url van je zelfgemaakte repository.

```
$ git clone git@github.com:csrdelft/csrdelft.nl
$ cd csrdelft.nl
$ git submodule init
$ git submodule update
```

### Stap 3.1
De hele filestructuur van de repository is nu gedownload op je computer. Een korte uitleg van wat welke folder betekent is te vinden op de pagina [Filestructuur](filestructuur.md).

## Stap 4: Installatie
Er zijn drie mogelijke manieren om te installeren, met Docker of met de hand. Als je actief gaat ontwikkelen aan de stek is het met de hand opzetten aan te raden.

_Over installatie met docker kun je in het bestand [Docker](installatie-docker.md) meer lezen._

Installeer Apache2 met PHP en MySQL. Op Windows is er XAMPP of wampserver, wat dit makkelijk maakt.

#### Apache2
Maak in je `hosts` (`/etc/hosts` of `C:\Windows\system32\drivers\etc\hosts`) bestand een verwijzing van `dev-csrdelft.nl` naar `localhost`.
Voeg bijvoorbeeld de volgende regel toe: `127.0.0.1 dev-csrdelft.nl`

De volgende configuratie werkt goed voor Apache2. (**Let op de** `php_value include_path ...`.)

In XAMPP (waarmee altijd XAMPP Control Panel wordt bedoeld): `Apache => config => <Browse>[Apache] => conf => extra => httpd-vhosts.conf` en plak het volgende:
```
<VirtualHost dev-csrdelft.nl:80>
    DocumentRoot "<repo root>\htdocs"
    ServerName dev-csrdelft.nl
    ServerAlias dev-csrdelft.nl
    ErrorLog "logs/dev-csrdelft.nl-error.log"
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
Enable `ldap` in `php.ini`

In XAMPP: `Apache => config => PHP (php.ini) => Zoek naar ldap => Haal de ; bij ;extension=ldap weg`

#### MySQL
We gaan nu een MySQL server opstarten, waar vervolgens de lokale database op runt.

In XAMPP: `MySQL => start`. Hopelijk start de MySQL server gelijk op. Stel de 3306 poort is bezet, dan zijn er 2 oplossingen:
1. Klik op de Netstat knop in XAMPP, kijk welk process port 3306 bezet houdt en kill dit programma via de Task Manager.
2. [Verander de poort voor de MySQL server](https://stackoverflow.com/questions/32173242/conflicting-ports-of-mysql-and-xampp). In XAMPP: `MySQL => config => my.ini`. Vervang de 3306 poort overal naar een ander poortnummer, bijvoorbeeld 3307. Ga dan in XAMPP zelf naar `config => Service and Port Settings => MySQL Tab` En verander de service naam `mysql` naar `mysqlxampp` en de main port 3306 naar 3307.

In de C:/XAMPP folder, ga naar `mysql\bin`. Open een terminal en typ het volgende commando:
```
./mysql.exe -u root -p
# of
mysql -u root -p (als het mysql commando al wel gedefinieerd is in een windows PATH variable.)
```
Je bent nu ingelogd op de MySQL server.
Maak vervolgens een database `csrdelft` aan.

```
CREATE USER 'csrdelft'@'localhost' IDENTIFIED BY 'bl44t';
CREATE DATABASE `csrdelft`;
GRANT ALL PRIVILEGES ON `csrdelft` . * TO 'csrdelft'@'localhost';
```

Als je database verbinding anders is dan gebruiker `csrdelft` met wachtwoord `bl44t` op host `localhost` en database `csrdelft`, voeg dan de dsn van je database toe aan `.env.local`

Switch nu naar de csrdelft database met het commando: `use csrdelft;`

**Plaats nu de export die je in stap 0 hebt gefixt in de database.** Doe dit als volgt:
```
source <repo root>\data\***.sql (Met *** voor de tabellen file);
source <repo root>\data\###.sql (Met ### voor de data file);
```

#### Dependencies
Download en installeer [Composer](https://getcomposer.org/) en [Yarn](https://classic.yarnpkg.com/en/docs/install).
Deze tools worden gebruikt om respectievelijk PHP en Javascript dependencies te installeren.

Open een terminal en voer het volgende commando uit:

```bash
composer update-dev
```

(Kijk in `composer.json` om te zien wat er precies gebeurt als je dit doet).

Tip: als je met javascript aan de gang gaat is het fijn om automatisch je javascript bestanden te builden, gebruik hier voor het volgende commando:

```bash
# Run build en blijf watchen
$ yarn run watch
# of (als de eerste niet goed werkt, dat kan op sommige systemen)
$ yarn run watch-poll
```

Download en installeer [ImageMagick](https://imagemagick.org/script/download.php). Dit wordt gebruikt om het fotoalbum goed te laten werken. Als je v7 van ImageMagick hebt geinstalleerd voeg dan `IMAGEMAGICK=magick` toe aan `.env.local`

#### Klaar
Ga nu naar `http://dev-csrdelft.nl`

Als je verse code hebt gepulld kan het handig zijn om `composer update-dev` nog een keer uit te voeren.

#### Cache (geavanceerd)

Dit is optioneel, maar kan helpen om je dev stek wat sneller te maken of om specifieke cache problemen te kunnen testen. Lees het [Cache](https://github.com/csrdelft/csrdelft.nl/wiki/Caching) document op de wiki voor meer info.
