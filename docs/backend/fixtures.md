---
layout: default
parent: Backend
nav_order: 1
title: Fixtures
---

# Fixtures

_Fixtures zijn een werk in uitvoering. Er zijn nu net genoeg fixtures om in te kunnen loggen. Er moeten er nog een boel bijgebouwd worden._

Er zijn fixtures om data in de database te laden zonder dat je een productiedatabase nodig hebt. Dit kan handing zijn voor het maken van tests.

De fixture generators gaan altijd uit van een verse database waar alle migraties op uitgevoerd zijn.

## Fixtures laden

Voordat je fixtures gaat laden is het belangrijk om te controleren of je huidige database weggegooid mag worden

Run `php bin/console doctirne:database:drop` om te checken welke database je nu in gebruik hebt en verander deze in `.env.local` als het niet klopt. Het is aanbevolen om een losse database te hebben waar de fixtures in geladen zijn.

1. Zet de database url naar een nieuwe database in .env.local
1. Run `php bin/console doctrine:database:drop --force` om de huidige database te droppen (hoeft niet de eerste keer).
1. Run `php bin/console doctrine:database:create` om de database aan te maken.
1. Run `php bin/console doctrine:migrations:migrate -n` om alle migraties uit te voeren.
1. Run `php bin/console doctrine:fixtures:load --no-interaction` om alle fixtures te laden.

De fixtures bevatten ook het account `x101` met wachtwoord `stek open u voor mij!`, dit account heeft PubCie rechten.

## Fixtures maken

Met de maker bundle kun je makkelijk nieuwe fixtures genereren. Run `php bin/console make:fixtures` om nieuwe fixtures te maken. In de map `lib/DataFixtures` staan alle fixtures. Je kan hier afkijken hoe het gedaan wordt.

Voor fixtures kun je ook `fzaninotto/faker` gebruiken om fake data te genereren.

