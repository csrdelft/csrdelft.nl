# Installatie van de stek

Volg dit stappenplan om de stek op je eigen computer te installeren. Wees precies met het uitvoeren van de commando's want een aantal instellingen zijn standaard geconfigureerd in de stek en als je daar van afwijkt moet je het in je eigen configuratie ook goed zetten.

Als je tegen problemen aan loopt tijdens het doorlopen van de installatie pas dit dan aan in dit document of voeg een kopje toe onder Foutopsporing.

## Stap 0: Programma's installeren
_Als je in de PubCie zit en je hebt geen toegang tot de database, zorg er dan voor dat je van iemand een dump van de database krijgt. Zonder de dump kun je ook met de testdatabase werken, maar dit is een minder goede ervaring. Je kan ook een dump van de profielfoto's vragen, deze heb je niet per se nodig, maar maakt je lokale stek iets mooier._

Installeer de volgende programma's:

- [wampserver](https://sourceforge.net/projects/wampserver/)
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

*Ben je geen lid van de PubCie, [maak een fork van de stek](https://github.com/csrdelft/csrdelft.nl/fork)*

Gebruik de GitHub Desktop client om `csrdelft/csrdelft.nl` te downloaden (of je eigen fork). Stel in dit programma ook je naam en email in (standaard waarden zijn meestal prima). Kijk goed in welke map de gedownloade code terecht komt, dit heb je later nodig. Standaard komt de code van GitHub Desktop in `C:\Users\feut\Documenten\GitHub\csrdelft.nl\` terecht.

**Zorg ervoor dat de code in een map komt die niet met Google Drive of OneDrive gesynced wordt, anders zit deze straks vol.**

<details>
<summary><strong>Command line</strong></summary>

Configureer je lokale git installatie met de goede gegevens, zo worden je veranderingen ook aan je toegekend. (Regels met een `$` er voor moeten uitgevoerd worden in powershell/bash)

```bash
git config --global user.name "John Doe"
git config --global user.email johndoe@example.com
```

Download de stek op je computer, als je net een fork hebt gemaakt gebruik dan de url van je zelfgemaakte repository.

```bash
git clone git@github.com:csrdelft/csrdelft.nl
cd csrdelft.nl
git submodule init
git submodule update
```

</details>

De hele filestructuur van de repository is nu gedownload op je computer. Een korte uitleg van wat welke folder betekent is te vinden op de pagina [Filestructuur](filestructuur.md).

## Stap 2: Installatie
Er zijn twee mogelijke manieren om te installeren, met Docker of met de hand. Als je actief gaat ontwikkelen aan de stek is het met de hand opzetten aan te raden.

Zorg dat je vanaf hier Apache2 en MariaDB, oftewel wampserver hebt draaien.

_Over installatie met docker kun je in het bestand [Docker](installatie-docker.md) meer lezen._

### 2.1: VirtualHost instellen

*Gaat er vanuit dat je Wampserver hebt geinstalleerd in stap 0*

Start Wampserver op. Rechtsonderin bij de icoontjes verschijnt wampserver. Als je rechts of links klikt op dit icoontje krijg je verschillende menus te zien.

Ga naar [VirtualHost Management](http://localhost/add_vhost.php) in wampserver. Voeg hier een nieuwe virtualhost toe met de naam `dev-csrdelft.nl` en als path de `htdocs` map in de repository. Klik op opslaan en rechts-klik op het wampserver icoon rechtsonderin en klik op `Tools > Restart DNS`.

> Als je de repository hebt gedownload in `C:\Users\feut\Documenten\GitHub\csrdelft.nl` zet dan de path op `C:/Users/feut/Documenten/GitHub/csrdelft.nl/htdocs` (let op de slashes).

Nu is de server ingesteld, nu moet de code nog goed geinstalleerd worden.

**Wampserver moet aan staan als je je lokale stek wil bekijken**

### 2.2: PhpStorm instellen

Open het project in PhpStorm. Als het goed is worden dan de `[composer] Startup` en `[yarn] Startup` taken uitgevoerd in de Run tab onderin. Als dit niet het geval is kun je ze zelf nog uitvoeren.

_Iedere keer bij het opstarten van PhpStorm worden de Startup tasks uitgevoerd, als je_

In de lijst met Configurations kun je allerlei interessante commando's vinden. Deze commando's corresponderen met `yarn` en `composer` commando's die je ook in je terminal kan uitvoeren.

![](https://i.imgur.com/0W5HlPq.png)

Installeer de volgende Plugins in PhpStorm (File > Settings... > Plugins):

* Symfony Support
* .env files support

<details>
<summary><strong>Command line</strong></summary>

De volgende commando's worden uitgevoerd om de boel te initialiseren en te updaten:

Javascript dependencies installeren:
```bash
yarn
```

PHP dependencies installeren:
```bash
composer install
```

Javascript & SCSS compileren:
```bash
yarn dev
```

</details>

### 2.3: Database instellen

*Dit gaat er vanuit dat je database een gebruiker `root` heeft zonder wachtwoord, dit is standaard bij een installatie van MySQL. Heb je je database beveiligd kopieer dan het `DATABASE_URL` veld uit `.env` naar `.env.local` en zet de gegevens goed.*

Voer vanuit PhpStorm het `[PHP] Maak Database` commando uit. Dit commando zorgt ervoor dat er een lege database wordt neergezet.

Voer daarna het `[PHP] Migraties` commando uit (let op dat je `yes` moet typen). Dit commando zorgt ervoor dat alle database tabellen worden neergezet.

Als je een dump hebt gekregen kun je deze nu importeren. Onder de database tab of met HeidiSQL kun je deze importeren.

Als je geen dump hebt (je zit niet in de PubCie), kun je de Fixtures laden met het `[PHP] Database Fixtures` commando

<details>
<summary><strong>Command line</strong></summary>

Je kan ook commando's in de commandline uitvoeren. Dan moet je deze commando's hebben.

Database aanmaken:
```bash
php bin/console doctrine:database:create
```

Database migraties:
```bash
php bin/console doctrine:migrations:migrate
```

Database verwijderen:
```bash
php bin/console doctrine:database:drop --force
```

</details>

### 2.3: Frontend code builden

De frontend code wordt met een los process gebuild. Hier wordt Typescript omgezet naar Javascript en SCSS naar CSS.

Voer hier voor het volgende commando uit.

```bash
yarn
yarn dev
```

Kijk ook in [Frontend Build](frontend.md) en [Typescript](typescript.md) voor meer info.

## Stap 3: Inloggen

Om in te loggen op je teststek kun je de wachtwoord vergeten flow gebruiken. In de testomgeving worden berichten die eigenlijk een mail zouden zijn als melding weergegeven. Op deze manier kan je snel een nieuw wachtwoord aanmaken.

Op deze manier kun je ook inloggen op andere accounts, zonder SU (switch user) te gebruiken (meestal is SU genoeg).

Bij de fixtures kun je uid `x101` gebruiken om in te loggen.

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
