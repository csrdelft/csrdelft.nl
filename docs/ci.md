# Github Actions CI

[Continuous Integration](https://en.wikipedia.org/wiki/Continuous_integration) is de praktijk van het automatisch samenvoegen van veranderingen van verschillende programmeurs.

We gebruiken [GitHub Actions](https://docs.github.com/en/actions) om automatische acties uit te voeren.

Kijk in de [Actions](https://github.com/csrdelft/csrdelft.nl/actions) tab in de repository voor de huidige status.

## Stappen

Er zijn een aantal losse workflows, deze draaien los van elkaar

- ![Build & Deploy](https://github.com/csrdelft/csrdelft.nl/workflows/Build%20&%20Deploy/badge.svg)
- ![Create Sentry Releases](https://github.com/csrdelft/csrdelft.nl/workflows/Create%20Sentry%20Releases/badge.svg)
- ![Sonarcloud analyse](https://github.com/csrdelft/csrdelft.nl/workflows/Sonarcloud%20analyse/badge.svg)

### Build & Deploy

De build doet een aantal stappen,
* Compileren van Typescript en Scss naar Javascript en css
* PHP dependencies installeren en de autoloader optimizen
* De nieuwe versie naar [csrdelft/productie](https://github.com/csrdelft/productie) pushen

### Create Sentry Releases

Maakt een nieuwe release in Sentry, hierdoor kunnen commits aan foutmeldingen geknoopt worden

### Sonarcloud analyse

Hier gaat Sonarcloud over de code heen om te checken of alles akkoord is.

Op [sonarcloud.io](https://sonarcloud.io/dashboard?id=csrdelft_csrdelft.nl) is informatie te vinden over wat sonarcloud allemaal gevonden heeft. Hier kun je ook komen door op de badge te klikken in README.md: [![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=csrdelft_csrdelft.nl&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=csrdelft_csrdelft.nl)
