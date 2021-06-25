---
layout: default
parent: Introductie
nav_order: 2
title: Filestructuur
---

# Filestructuur uitleg

Hier wordt voor iedere map kort uitgelegd wat het doel van de map is.

* `bin`: Scripts die worden gerunt
  * `ci`: Scripts voor [Travis CI](../deploy/ci.md)
  * `cron`: Scripts die iedere dag of maand worden uitgevoerd
  * `dev`: Scriptjes voor tijdens development, worden niet gebruikt
* `config`: Configuratie bestanden (voornamelijk `yaml` bestanden)
  * `custom`: Configuratie die onder specifieke voorwaarden geladen wordt
  * `instellingen`: Hier worden LidInstelling/Instelling/LidToestemming waardes gedefinieerd
  * `packages`: Configuratie van Symfony packages.
  * `routes`: Alle routes van de stek staan in deze map (Zie [Request](request.md))
* `data`: Bevat de database dump, foto's en andere informatie van de stek
* `db/doctrine_migrations`: Database migraties waarmee de database aangepast wordt. Migraties kunnen ook een oude staat terugrollen, zie [Migraties](../deploy/migraties.md).
* `docker`: Docker images voor development aan de stek via Docker
* `docs`: De map waar deze site van gegenereerd wordt.
* `htdocs`: Bestanden die de webserver inlaadt. Alle submodules zijn hier ook te vinden. Zie [Submodule](../submodule.md) voor bestanden die niet hier onder worden besproken.
  * `actueel`: Redirect om oude urls te laten werken
  * `API`: De API van de app en sponsor downloads
  * `dist`: Het resultaat van webpack
  * `images`: Een aantal plaatjes die in de repository staan en die niet door webpack worden opgepakt
  * `.htaccess`: Stelt in dat alle requests naar `index.php` moeten gaan als er geen bestand bij wordt gevonden.
  * `index.php`: Hét aanpsreekpunt van de stek
  * `manifest.json` & `robots.txt`: Info bestandjes voor zoekmachines/browsers
* `lib`: Alle PHP bestanden
* `node_modules`: **Niet echt relevant.** Modules die yarn (package manager) gebruikt
* `assets`: [Typescript](../frontend/typescript.md) / [Scss](../frontend/styles.md) / etc.
* `sessie`: **Niet echt relevant.** Map waar sessiebestanden in worden opgeslagen.
* `templates`: [Symfony Twig](../backend/twig.md) templates. Vervolg op Blade templates
* `tests`: Tests voor de stek. Wordt niet zoveel mee gedaan en er wordt vrij weinig getest.
* `var`: De cache map van Symfony. Als deze mist wordt deze gegenereerd (als je in dev mode zit). Als er iets stuk is of als er klassen niet gevonden kunnen worden kan het helpen om deze map weg te gooien.
* `vendor`: **Niet echt relevant.** Modules die Composer (package manager) gebruikt.

## Bestanden in `/`

Er staan veel bestanden in `/`, ze zijn hier met allerlei redenen en kunnen vaak niet zomaar naar een andere map verplaatst worden.

* `.dockerignore`: Voor Docker, voorkomt dat de hele map naar Docker wordt gestuurd bij initialiseren van Docker (dit zou namelijk erg lang duren).
* `.editorconfig`: De regels waar de editor zich aan houdt, bijv dat we tabs gebruiken om in te springen behalve voor yaml bestanden.
* `.env` / `.env.dev` / `.env.prod`: Zie [Configuratie](configuratie.md)
* `.eslintrc.yaml`: Configuratie van de JavaScript stijl regels
* `.gitattributes`: Specifiek git bestand die o.a. line endings in kan stellen voor specifieke bestanden
* `.gitignore`: Geeft aan welke bestanden niet op GitHub terecht moeten komen.
* `.gitmodules`: Git [Submodules](../submodule.md)
* `.travis.yml`: Configuratie voor [Travis CI](../deploy/ci.md)
* `composer.json`: Definieert PHP dependencies (Zie [getcomposer.org](https://getcomposer.org/))
* `composer.lock`: Zet de PHP dependencies op een specifieke versie (automatisch gegenereerd)
* `CONTRIBUTING.md`: Lees meer over hoe je bijdraagt aan de stek
* `docker-compose.yml`: Configuratie voor [Docker](installatie-docker.md)
* `package.json`: Defineert JavaScript dependencies (Zie [yarnpkg.com](https://yarnpkg.com/) en [npmjs.org](https://npmjs.org/))
* `phpstan.autoload.php` / `phpstan.neon`: Configuratie voor phpstan static analysis (Zie [phpstan.org](https://phpstan.org/))
* `phpunit.init.php`: Configuratie van phpunit, de test tool (Zie [phpunit.de](https://phpunit.de/))
* `README.md`: LEES MIJ
* `sonar-project.properties`: Configuratie van [SonarCloud.io](https://sonarcloud.io/dashboard?id=csrdelft_csrdelft.nl)
* `symfony.lock`: Symfony variant van composer.lock
* `tsconfig.json`: Configuratie van [Typescript](../frontend/typescript.md)
* `tslint.json`: Configuratie van de Typescript style
* `webpack.config.js`: Configuratie van Webpack, de build tool van de frontend. (Zie [webpack.js.org](https://webpack.js.org/))
* `yarn.lock`: Zet de JavaScript dependencies op een specifieke versie (automatisch gegenereerd)

