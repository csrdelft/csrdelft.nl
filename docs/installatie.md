# Installatie van de stek

*Alle commando's in deze uitleg worden uitgevoerd vanuit de hoofdmap van de repository (zodra je de broncode hebt binnengehaald). Op Windows werkt Powershell goed, als je op Linux zit weet je waarschijnlijk al welke shell nice is en heb je daar een uitgesproken mening over.*

Volg dit stappenplan om de stek op je eigen computer te installeren. Wees precies met het uitvoeren van de commando's want een aantal instellingen zijn standaard geconfigureerd in de stek en als je daar van afwijkt moet je het in je eigen configuratie ook goed zetten.

Als je tegen problemen aan loopt tijdens het doorlopen van de installatie pas dit dan aan in dit document of voeg een kopje toe onder Foutopsporing.

Als je de stek al eens eerder hebt geinstalleerd en je wil de boel verversen gebruikt dan het volgende commando:

```bash
composer update-dev
```


## Stap 0: Programma's installeren
Zorg ervoor dat je een database dump krijgt van de PubCie, zonder deze dump is het erg ingewikkeld om de boel draaiende te krijgen. Zorg er ook voor dat je een plaetjes dump hebt.

Installeer de volgende programma's:

- [xampp](https://www.apachefriends.org/download.html) of [wampserver](https://sourceforge.net/projects/wampserver/)
  - Komt met Apache2, Mariadb en PHP
  - wampserver komt met een iets vriendelijkere gebruikersinterface en wordt verder in deze uitleg gebruikt.
  - Zorg ervoor dat je een versie met PHP 7.3 installeert, want dit is wat de productie versie van de stek ook draait. (7.4 is op zich ook prima)
	- Zorg ervoor dat je MariaDB installeert en niet MySql, deze twee databases lijken erg op elkaar maar hebben allerlei subtiele verschillen.
	- In productie draait MariaDB 10.3, installeer deze als je zeker wil zijn dat alles hetzelfde is.
- [git](https://git-scm.com)
  - Om de sourcecode te downloaden en veranderingen te maken
  - De [GitHub Desktop](https://desktop.github.com/) is een toegankelijke manier van git gebruiken
- [composer](https://getcomposer.org)
  - De PHP dependency manager
- [Node.js](https://nodejs.org/en/)
  - Een JS runtime
  - De LTS-versie is prima voor wat wij doen, en is aanbevolen tenzij je expliciet dingen wil die niet in de LTS zitten.
- [yarn](https://yarnpkg.com/getting-started/install)
  - De JS dependency manager
- [PhpStorm](https://www.jetbrains.com/phpstorm/)
  - Een goede IDE van Jetbrains, pro versie is gratis voor studenten
  - [Visual Studio Code](https://code.visualstudio.com/) is een redelijk alternatief, maar gebruikt dit alleen als je je er echt thuis in voelt.
- [HeidiSQL](https://www.heidisql.com/download.php)
  - Een chille sql client
  - Veel sql clients kunnen niet met Syrinx (productie) verbinden, deze wel

## Stap 1: Ophalen van de broncode

Maak een account aan op [GitHub](https://github.com) als je dat nog niet eerder hebt gedaan.

Configureer je lokale git installatie met de goede gegevens, zo worden je veranderingen ook aan je toegekend. (Regels met een `$` er voor moeten uitgevoerd worden in powershell/bash)

```
git config --global user.name "John Doe"
git config --global user.email johndoe@example.com
```

*Ben je geen lid van de PubCie, [maak een fork van de stek](https://github.com/csrdelft/csrdelft.nl/fork)*

Download de stek op je computer, als je net een fork hebt gemaakt gebruik dan de url van je zelfgemaakte repository.

```
git clone git@github.com:csrdelft/csrdelft.nl
cd csrdelft.nl
git submodule init
git submodule update
```

De hele filestructuur van de repository is nu gedownload op je computer. Een korte uitleg van wat welke folder betekent is te vinden op de pagina [Filestructuur](filestructuur.md).

## Stap 2: Installatie
Er zijn twee mogelijke manieren om te installeren, met Docker of met de hand. Als je actief gaat ontwikkelen aan de stek is het met de hand opzetten aan te raden.

Zorg dat je vanaf hier Apache2 en MariaDB, oftewel wampserver/xampp hebt draaien.

_Over installatie met docker kun je in het bestand [Docker](installatie-docker.md) meer lezen._

### 2.1: PHP dependencies installeren

Voer het volgende commando uit om php dependencies te installeren.

```bash
composer install
```

### 2.2: Database instellen

*Dit gaat er vanuit dat je database een gebruiker `root` heeft zonder wachtwoord, dit is standaard bij een installatie van MySQL. Heb je je database beveiligd kopieer dan het `DATABASE_URL` veld uit `.env` naar `.env.local` en zet de gegevens goed.*

Voer vanaf de command line het volgende commando uit om een database te maken:

```bash
php bin/console doctrine:database:create
```

Voer vanaf de command line het volgende commando uit om de tabellen in de database te laden:

```bash
php bin/console doctrine:migrations:migrate
```

Als je een dump hebt gekregen kun je deze nu importeren met HeidiSQL, DataGrip of een andere SQL client die je graag gebruikt. Als je geen dump hebt gekregen kun je de [fixtures](fixtures.md) laden om te kunnen testen op test-data.

### 2.3: Frontend code builden

De frontend code wordt met een los process gebuild. Hier wordt Typescript omgezet naar Javascript en SCSS naar CSS.

Voer hier voor het volgende commando uit.

```bash
yarn
yarn dev
```

Kijk ook in [Frontend Build](frontend.md) en [Typescript](typescript.md) voor meer info.

### 2.4: VirtualHost instellen

*Gaat er vanuit dat je Wampserver hebt geinstalleerd in stap 0*

Start Wampserver op. Rechtsonderin bij de icoontjes verschijnt wampserver. Als je rechts of links klikt op dit icoontje krijg je verschillende menus te zien.

Ga naar [VirtualHost Management](http://localhost/add_vhost.php) in wampserver. Voeg hier een nieuwe virtualhost toe met de naam `dev-csrdelft.nl` en als path de `htdocs` map in de repository. Klik op opslaan en rechts-klik op het wampserver icoon rechtsonderin en klik op `Tools > Restart DNS`.

> Als je de repository hebt gedownload in `C:\users\feut\Projecten\csrdelft.nl` zet dan de path op `C:/users/feut/Projecten/csrdelft.nl/htdocs`.

Als je nu naar [`http://dev-csrdelft.nl`](http://dev-csrdelft.nl) als je alles goed hebt gedaan wordt je nu begroet door de externe stek en kun je inloggen met dezelfde gegevens als op de productie stek. Of met gebruiker `x101` met wachtwoord `stek open u voor mij!` als je de fixtures hebt geladen.

*Wampserver moet sowieso aan staan als je je lokale stek wil bekijken*

## Extra dingen

### Imagemagick

[ImageMagick](https://imagemagick.org/script/download.php) wordt gebruikt in het fotoalbum. Om dingen in het fotoalbum te kunnen testen, moet je het installeren. Als je v7 van ImageMagick hebt geinstalleerd voeg dan `IMAGEMAGICK=magick` toe aan `.env.local`

### Cache

Dit is optioneel, maar kan helpen om je dev stek wat sneller te maken of om specifieke cache problemen te kunnen testen. Lees het [Cache](caching.md) document voor meer info.

### Xdebug

[Xdebug](https://xdebug.org/download) is een superhandige tool om PHP code te kunnen debuggen. In PhpStorm bij instellingen (Ctrl+Alt+S) kun je onder **Languages & Frameworks > PHP > Debug** meer info vinden over de installatie van Xdebug. Het kan lonen om Xdebug uit te zetten als je het niet gebruikt, want deze extensie kan PHP heel erg langzaam maken.

### OPcache

Sommige installaties komen standaard met [OPcache](https://www.php.net/manual/en/book.opcache.php), deze extensie zorgt ervoor dat PHP code sneller wordt uitgevoerd als deze niet is veranderd. Dit kan ontwikkelen een stuk sneller maken.

## Foutopsporing

### MySQL wil niet starten

Als het icoontje van Wampserver oranje is kan het zijn dat bepaalde poorten in gebruik zijn. Hier voor kun je de tools in Wampserver gebruiken. Dit menu kun je openen door te rechtsklikken op het Wampserver icoontje rechts onderin. Hier kun je de poort van 3306 aanpassen naar iets anders.

Als je de poort hebt aangepast kopieer dan ook de regel met `DATABASE_URL` van `.env` naar `.env.local` (maak deze aan als deze nog niet bestaat) en verander 3306 naar de poort die je gekozen hebt.
