# Travis CI/CD

[Continuous Integration](https://en.wikipedia.org/wiki/Continuous_integration) is de praktijk van het automatisch samenvoegen van veranderingen van verschillende programmeurs.

We gebruiken Travis om automatisch alles klaar te zetten om het op Syrinx te deployen. In het bestand `.travis.yml` is het process gedefinieerd.

## Stappen

Het proces op Travis heeft twee onderdelen, Build en Analysis, na de eerste stap

### Build

De build doet een aantal stappen,
* Compileren van Typescript en Scss naar Javascript en css
* PHP dependencies installeren
* Blade Templates compileren
* De PHP autoloader optimizen zodat deze sneller werkt
* De nieuwe versie naar [csrdelft/productie](https://github.com/csrdelft/productie) pushen

Als dit allemaal gelukt is gaat de volgende badge op groen: [![Build Status](https://travis-ci.org/csrdelft/csrdelft.nl.svg?branch=master)](https://travis-ci.org/csrdelft/csrdelft.nl)

### Analysis

Hier gaat Sonarcloud over de code heen om te checken of alles akkoord is.

Op [sonarcloud.io](https://sonarcloud.io/dashboard?id=csrdelft_csrdelft.nl) is informatie te vinden over wat sonarcloud allemaal gevonden heeft. Hier kun je ook komen door op de badge te klikken in README.md: [![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=csrdelft_csrdelft.nl&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=csrdelft_csrdelft.nl)
