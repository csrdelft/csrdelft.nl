---
layout: default
parent: Backend
nav_order: 1
title: Tests
---

# Tests

Er zijn twee soorten tests in de stek, unit tests en functionele tests. Unit tests testen bijvoorbeeld een functie. Functionele tests testen of pagina's bezoekbaar zijn en of klikken op linkjes werkt zoals verwacht.

Chromedriver is te downloaden van https://chromedriver.chromium.org/

## Tests runnen

1. Voeg `APP_ENV=test` toe aan je `.env.local` bestand.
2. Maak een `.env.test.local` bestand aan.

   ```
     DATABASE_URL=mysql://root@127.0.0.1:3306/csrdelft_test

     PANTHER_CHROME_BINARY="C:\Program Files\Google\Chrome\Application\chrome.exe"
     PANTHER_CHROME_DRIVER_BINARY="<path naar chromedriver>\chromedriver.exe"
     PANTHER_NO_HEADLESS=true
     PANTHER_WEB_SERVER_DIR=<path naar project>\csrdelft.nl\htdocs
   ```

3. Maak een db in te test environment
   ```shell script
   # Maak de database
   php bin/console doctrine:database:create
   # Maak de tabellen
   php bin/console doctrine:migrations:migrate
   # Vul de tabellen met testdata. Als de database al bestaat is allen het volgende commando genoeg om de data te verversen.
   php bin/console doctrine:fixtures:load
   ```
4. Run `php bin/phpunit` om alle tests te runnen. PhpStorm kan ook losse tests uitvoeren.

Vergeet niet om je `APP_ENV` weer terug te zetten om naar de `dev` instellingen te gaan.

### Panther (Browser) tests

Panther is gebasseerd op php-webdriver en dit maakt weer gebruik van Selenium.

## Tests maken

In de map `tests` kun je nieuwe tests toevoegen. Kijk vooral af bij andere tests hoe je het moet aanpakken. We gebruiken [phpunit](https://phpunit.de) voor de tests.

### Test Base Classes

Er zijn een aantal base classes die interessant zijn voor tests

1. `PHPUnit\Framework\TestCase` Voor simpele tests die alleen functies testen.
1. `Symfony\Bundle\FrameworkBundle\Test\KernelTestCase` Voor tests die onderdelen van de stek testen en daar bijvoorbeeld database functionaliteit nodig hebben
1. `Symfony\Bundle\FrameworkBundle\Test\WebTestCase` Voor tests die requests uitvoeren op de stek en controleren of het resultaat klopt.
