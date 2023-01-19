---
layout: default
parent: Backend
nav_order: 8
title: Dependencies
---

# Dependencies

Dependencies worden geladen door [composer](./composer.md). Dit document legt uit waar dependencies voor worden gebruikt.

## Require

Dependencies die worden gebruikt als de stek draait.

| Naam                                 | Versie     | Beschrijving                                                                 |
| ------------------------------------ | ---------- | ---------------------------------------------------------------------------- |
| csrdelft/bb                          | 1.3.5      | Onze BB parser, wordt voor het forum en het cms gebruikt.                    |
| easyrdf/easyrdf                      | ^0.9.1     | Gebruikt in de bieb voor de boekimporter om isbn info van worldcat te lezen. |
| beberlei/doctrineextensions          | ^1.2       |
| clegginabox/pdf-merger               | dev-master |
| composer/package-versions-deprecated | 1.11.99.1  |
| doctrine/annotations                 | ^1.0       | Maakt het mogelijk om doctrine te configureren met annotaties in php         |
| doctrine/doctrine-bundle             | ^2.3       | Integreer doctrine met symfony                                               |
| doctrine/doctrine-migrations-bundle  | ^3.1       | Integreer doctrine migrations met symfony                                    |
| doctrine/orm                         | ^2.8       | Doctrine is het orm dat we gebruiken.                                        |
| endroid/qr-code                      | ^4.1       | Genereert qr codes voor remote-login                                         |
| firebase/php-jwt                     | ^5.0       | Genereert en check jwt tokens voor api v2                                    |
| globalcitizen/php-iban               | ^2.6       | IBAN check                                                                   |
| google/apiclient                     | ^2.0       | Google Contacts Sync                                                         |
| jakeasmith/http_build_url            | ^1         | Wordt in curl_follow_location gebruikt (Moet verwijderd worden)              |
| maknz/slack                          | ^1.7       | Errors naar slack                                                            |
| nelmio/cors-bundle                   | ^2.1       |
| parsecsv/php-parsecsv                | ^1.2       | Parsed csv voor de civisaldo afschrijven tool.                               |
| phpdocumentor/reflection-docblock    | ^5.2       |
| sensio/framework-extra-bundle        | ^5.5       | Makkelijkere controllers in symfony                                          |
| sentry/sentry-symfony                | ^3.5       | Sentry integratie in Symfony                                                 |
| symfony/cache                        | ^5.0       | Symfony Cache                                                                |
| symfony/config                       | ^5.0       | Configureer symfony met yaml                                                 |
| symfony/dotenv                       | ^5.0       | Laad server-specifieke configuratie van een .env bestand                     |
| symfony/flex                         | ^1.4       | Vergemakkelijkt het installeren van meer Symfony spullen                     |
| symfony/form                         | ^5.0       | Formulieren in Symfony                                                       |
| symfony/framework-bundle             | ^5.0       | Algemene symfony package                                                     |
| symfony/http-foundation              | ^5.0       | Requests in symfony                                                          |
| symfony/mime                         | ^5.0       | Emails in Symfony (ongebruikt?)                                              |
| symfony/monolog-bundle               | ^3.5       | Logframework in symfony                                                      |
| symfony/property-access              | ^5.0       |
| symfony/property-info                | ^5.0       |
| symfony/proxy-manager-bridge         | ^5.0       |
| symfony/routing                      | ^5.0       | Symfony router                                                               |
| symfony/security-bundle              | ^5.0       | Inloggen in de stek                                                          |
| symfony/security-csrf                | ^5.0       | CSRF tokens voor post requests                                               |
| symfony/serializer                   | ^5.0"      | Betere json serialize                                                        |
| symfony/templating                   | ^5.0       | Twig integratie in symfony                                                   |
| symfony/translation                  | ^5.0       | Vertalingen (voor de externe stek)                                           |
| symfony/twig-bundle                  | ^5.0       | Twig templates                                                               |
| symfony/uid                          | ^5.0       |
| symfony/var-dumper                   | ^5.0       |
| symfony/yaml                         | ^5.0       | Yaml lezer                                                                   |
| tecnickcom/tcpdf                     | ^6.4       | Genereert pdfs voor declaraties                                              |
| league/oauth2-server-bundle          | ^0.4.1     | OAuth2 integratie in de stek                                                 |
| twig/cssinliner-extra                | ^3.3       |
| twig/extra-bundle                    | ^3.2       | Aantal coole goodies voor twig.                                              |
| twig/intl-extra                      | ^3.2       | Standaard ext-intl functies in twig                                          |
| zumba/json-serializer                | ^3.0       | Betere JSON serializer die ook klassen kan serializen                        |

## Require dev

Dev dependencies worden gebruikt in de lokale teststek of bij het uitvoeren van de tests.

| Naam                                   | Versie | Beschrijving                                                 |
| -------------------------------------- | ------ | ------------------------------------------------------------ |
| [doctrine/doctrine-fixtures-bundle]()  | ^3.3   | Voor het laden van fixtures                                  |
| [symfony/maker-bundle]()               | ^1.19  | Hulpfuncties voor de console om nieuwe componenten te maken  |
| [fzaninotto/faker]()                   | ^1.9   | Laden van neppe data voor de fixtures                        |
| [symfony/phpunit-bridge]()             | ^5.1   | Laat symfony en phpunit samenwerken                          |
| [phpunit/phpunit]()                    | 8.5.18 | PhpUnit is een php testing framework                         |
| [spatie/phpunit-snapshot-assertions]() | ^3.0   | Maak het makkelijk om met phpunit snapshot tests te maken    |
| [symfony/browser-kit]()                | ^5.0   | Browsertests met symfony                                     |
| [symfony/css-selector]()               | ^5.0   | Css selector in browsertests                                 |
| [symfony/panther]()                    | ^0.8.0 | Browsertests in een daadwerkelijke browser                   |
| [weirdan/doctrine-psalm-plugin]()      | ^1.0   | Doctrine + Psalm static analysis                             |
| vimeo/psalm                            | ^4.7   | Een static analysis tool                                     |
| [psalm/plugin-symfony]()               | ^2.2   | Symfony + Psalm static analysis                              |
| [symfony/stopwatch]()                  | ^5.4   | Symfony stopwatch, voor de web profiler                      |
| [symfony/web-profiler-bundle]()        | ^5.4   | Die mooie balk die je onderaan je scherm hebt op je dev stek |

## PHP extensies

De volgende tabel bevat php extensies, de meesten worden standaard geladen bij een PHP installatie. Composer kan controleren of deze extensies aanwezig zijn.

Gebruik de commandline flag `--ignore-platform-reqs` om deze check uit te zetten.

| Naam                                                              | Beschrijving                       | Type                       |
| ----------------------------------------------------------------- | ---------------------------------- | -------------------------- |
| [ext-PDO](https://www.php.net/manual/en/book.pdo.php)             | PDO functies                       | bundled                    |
| [ext-curl](https://www.php.net/manual/en/book.curl.php)           | cURL                               | **external**               |
| [ext-dom](https://www.php.net/manual/en/book.dom.php)             | DOM Document Object Model          | external                   |
| [ext-exif](https://www.php.net/manual/en/book.exif.php)           | Lees informatie over afbeeldingen  | bundled                    |
| [ext-gd](https://www.php.net/manual/en/book.image.php)            | Image processing                   | bundled                    |
| [ext-hash](https://www.php.net/manual/en/book.hash.php)           | Wachtwoorden hashen                | core                       |
| [ext-iconv](https://www.php.net/manual/en/book.iconv.php)         | iconv                              | bundled                    |
| [ext-intl](https://www.php.net/manual/en/book.intl.php)           | Internationalisering               | bundled                    |
| [ext-json](https://www.php.net/manual/en/book.json.php)           | JSON                               | core                       |
| [ext-libxml](https://www.php.net/manual/en/book.libxml.php)       | xml laden en schrijven, GoogleSync | **external**               |
| [ext-mysqli](https://www.php.net/manual/en/book.mysqli.php)       | Mysqli                             | **external** (ongebruikt?) |
| [ext-openssl](https://www.php.net/manual/en/book.openssl.php)     | Random nummer generator            | **external**               |
| [ext-pdo_mysql](https://www.php.net/manual/en/ref.pdo-mysql.php)  | PDO Mysql                          | **external**               |
| [ext-simplexml](https://www.php.net/manual/en/book.simplexml.php) | xml laden, GoogleSync              | **external**               |
| [ext-zip](https://www.php.net/manual/en/book.zip.php)             | Declaratiegenerator                | **external** (ongebruikt)  |
