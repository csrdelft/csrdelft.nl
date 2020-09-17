# Tests

Er zijn twee soorten tests in de stek, unit tests en functionele tests. Unit tests testen bijvoorbeeld een functie. Functionele tests testen of pagina's bezoekbaar zijn en of klikken op linkjes werkt zoals verwacht.

## Tests runnen

1. Maak een db in te test environment
    ```shell script
    bin/console doctrine:database:create --env test
    bin/console doctrine:migrations:migrate --env test
    bin/console doctrine:fixtures:load --env test
    ```
1. Run `php bin/phpunit` om alle tests te runnen. PhpStorm kan ook losse tests uitvoeren.

## Tests maken

In de map `tests` kun je nieuwe tests toevoegen. Kijk vooral af bij andere tests hoe je het moet aanpakken. We gebruiken [phpunit](https://phpunit.de) voor de tests.

### Test Base Classes

Er zijn een aantal base classes die interessant zijn voor tests

1. `PHPUnit\Framework\TestCase` Voor simpele tests die alleen functies testen.
1. `Symfony\Bundle\FrameworkBundle\Test\KernelTestCase` Voor tests die onderdelen van de stek testen en daar bijvoorbeeld database functionaliteit nodig hebben
1. `Symfony\Bundle\FrameworkBundle\Test\WebTestCase` Voor tests die requests uitvoeren op de stek en controleren of het resultaat klopt.


