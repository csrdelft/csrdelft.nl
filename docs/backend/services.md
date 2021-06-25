---
layout: default
parent: Backend
nav_order: 1
title: Services
---

# Services

De stek bestaat uit een hele hoop services die aan elkaar hangen. Een Service is een PHP klasse die geregistreerd is in het Symfony framework.

In het bestand `config/services.yaml` staan alle stek-specifieke services gedefineerd.

Controller, Repository, Service klassen zijn allemaal services in de stek.

Je kan services niet zomaar aanroepen met `new`, want vaak heb je nog allerlei andere dingen nodig zoals de huidige database verbinding. Hier wordt dependency injection voor gebruikt.

## Dependency Injection

[Dependency Injection](https://nl.wikipedia.org/wiki/Dependency_injection) is een manier om klassen losjes te koppelen. Je geeft aan welke klassen je als klasse nodig hebt en het framework zorgt ervoor dat deze klassen aan jou gegeven in de constructor.

**Een belangrijk ding is dat je in de constructor van een service NOOIT aannames mag doen over huidige staat van de applicatie. Het kan dus zijn dat de database nog niet is verbonden of dat er helemaal geen database is**

Als je een willekeurige service opent zie je dat deze in de constructor allerlei andere services als parameter aangeeft.

## ContainerFacade

ContainerFacade is een klasse die je kan gebruiken om op plekken waar je niet in een een service zit en dus geen Dependency Injection kan gebruiken toch bij de services te komen.

Als je ContainerFacade nodig hebt, heb je waarschijnlijk niet zulke nette code geschreven of heb je iets aangepast in een bestaand systeem dat nog geupdate moet worden.

Uit ContainerFacade kun je alle services opvragen die `public` zijn. Met de Symfony extensie in PhpStorm kun je deze makkelijk herkennen.
