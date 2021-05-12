---
layout: default
parent: Backend
nav_order: 1
title: Composer
---

# Composer

Composer is de dependency manager voor PHP code. Zie [Frontend](../introductie/frontend.md) voor de dependency manager voor Javascript code.

In het bestand `composer.json` staat alle informatie over Composer.

## Dependencies

Er zijn een aantal dependencies, deze staan ook in `composer.json`. Deze dependencies zijn allemaal PHP code die we gebruiken en inladen. Kopieer nooit blokken code van Github als je ook een dependency kan toevoegen. Dependencies zijn met composer heel makkelijk te updaten.

In `composer.json` staan ook dependencies gedefinieerd die beginnen met `ext-...`. Deze dependencies geven aan dat specifieke PHP extensies nodig zijn. Met het volgende commando kun je controleren of jouw installatie alle extensies heeft geinstalleerd:

```
composer check-platform-reqs
```

## Autoloader

Verschillende talen hebben verschillende oplossingen voor het inladen van bestanden. In PHP wordt de autoloader gebruikt, als een klasse niet is ingeladen wordt de autoloader aangeroepen met de FQCN (Fully Qualified Class Name) van deze klasse. De autoloader zorgt er dan voor dat het juiste PHP bestand wordt ingeladen. Composer komt met een [PSR-4](https://www.php-fig.org/psr/psr-4/) autoloader.

Deze autoloader gaat voor alle klassen die beginnen met `CsrDelft\ ` kijken of de klasse te vinden is in de `lib/` map.

## Scripts

Met composer kun je allerlei handige scripts definieren. Zo zijn er bijvoorbeeld het `update-prod` en `update-dev` script om makkelijk productie of development te kunnen updaten.

Bedenk als je hier iets aan wil toevoegen of je niet beter een [Console Command](https://symfony.com/doc/current/console.html) kan toevoegen (zie de `lib/command` map).
