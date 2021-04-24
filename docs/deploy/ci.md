---
layout: default
parent: Deploy
nav_order: 1
title: Github Actions
---

# Github Actions CI

[Continuous Integration](https://en.wikipedia.org/wiki/Continuous_integration) is de praktijk van het automatisch samenvoegen van veranderingen van verschillende programmeurs.

We gebruiken [GitHub Actions](https://docs.github.com/en/actions) om automatische acties uit te voeren.

Kijk in de [Actions](https://github.com/csrdelft/csrdelft.nl/actions) tab in de repository voor de huidige status.

## Stappen

Er zijn een aantal losse workflows, deze draaien los van elkaar

[![CI](https://github.com/csrdelft/csrdelft.nl/actions/workflows/ci.yml/badge.svg)](https://github.com/csrdelft/csrdelft.nl/actions/workflows/ci.yml)
[![Create Sentry Releases](https://github.com/csrdelft/csrdelft.nl/actions/workflows/sentry.yml/badge.svg)](https://github.com/csrdelft/csrdelft.nl/actions/workflows/sentry.yml)
[![Sonarcloud analyse](https://github.com/csrdelft/csrdelft.nl/actions/workflows/sonar.yml/badge.svg)](https://github.com/csrdelft/csrdelft.nl/actions/workflows/sonar.yml)

### CI

De build doet een aantal stappen,
* Compileren van Typescript en Scss naar Javascript en css
* PHP dependencies installeren en de autoloader optimizen
* De nieuwe versie naar [csrdelft/productie](https://github.com/csrdelft/productie) pushen
* Tests draaien

### Create Sentry Releases

Maakt een nieuwe release in Sentry, hierdoor kunnen commits aan foutmeldingen geknoopt worden

### Sonarcloud analyse

Hier gaat Sonarcloud over de code heen om te checken of alles akkoord is. Hierna wordt het rapport naar sonarcloud.io gestuurd.

Op [sonarcloud.io](https://sonarcloud.io/dashboard?id=csrdelft_csrdelft.nl) is informatie te vinden over wat sonarcloud allemaal gevonden heeft. Hier kun je ook komen door op de badge te klikken in README.md: [![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=csrdelft_csrdelft.nl&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=csrdelft_csrdelft.nl)
