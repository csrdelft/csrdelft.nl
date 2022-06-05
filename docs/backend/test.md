---
layout: default
parent: Backend
nav_order: 1
title: Tests
---

# Tests

Er zijn twee soorten tests in de stek, unit tests en functionele tests. Unit tests testen bijvoorbeeld een functie. Functionele tests testen of pagina's bezoekbaar zijn en of klikken op linkjes werkt zoals verwacht.

## Tests runnen

1. Stel een andere database in dan de database waar je normaal op test, door `DATABASE_URL` te zetten in `.env.local`.
   ```
   DATABASE_URL=mysql://root@127.0.0.1:3306/csrdelft_test
   ```
1. Maak een db in te test environment
   ```shell script
   # Als de database al bestaat je en je hem wil verversen
   php bin/console doctrine:database:drop --force
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```
1. Run `php bin/phpunit` om alle tests te runnen. PhpStorm kan ook losse tests uitvoeren.

### Panther (Browser) tests

Om Panther tests te runnen moet je Panther naar chrome en chromedriver wijzen. Dit kun je instellen in `.env.test.local`

```
PANTHER_CHROME_BINARY="C:\Program Files\Google\Chrome\Application\chrome.exe"
PANTHER_CHROME_DRIVER_BINARY="<path naar chromedriver>\chromedriver.exe"
PANTHER_NO_HEADLESS=true
```

Chromedriver is te downloaden van https://chromedriver.chromium.org/

## Tests maken

In de map `tests` kun je nieuwe tests toevoegen. Kijk vooral af bij andere tests hoe je het moet aanpakken. We gebruiken [phpunit](https://phpunit.de) voor de tests.

### Test Base Classes

Er zijn een aantal base classes die interessant zijn voor tests

1. `PHPUnit\Framework\TestCase` Voor simpele tests die alleen functies testen.
1. `Symfony\Bundle\FrameworkBundle\Test\KernelTestCase` Voor tests die onderdelen van de stek testen en daar bijvoorbeeld database functionaliteit nodig hebben
1. `Symfony\Bundle\FrameworkBundle\Test\WebTestCase` Voor tests die requests uitvoeren op de stek en controleren of het resultaat klopt.
