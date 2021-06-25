---
layout: default
parent: Introductie
nav_order: 2
title: Request
---

# Request

Dit document beschrijft een standaard request naar de stek.

![Request flow op hoog niveau](../request/01_request_overzicht.png)

Het bestand `htdocs/index.php` is het aanspreekpunt van de stek en bevat drie stappen. Deze stappen zijn hier onder beschreven.

## Setup

![Setup van een request](../request/02_request_setup.png)

De setup van een request is voor iedere request hetzelfde. Bij de setup wordt alles klaar gezet voor Symfony en wordt gecontroleerd met wat voor gebruiker we te maken hebben. Veel van dit proces wordt gecached, waardoor het vlug kan gebeuren.

De bootstrap stap wordt gedaan in `config/bootstrap.php` en er is hier meer info over te vinden door de documentatie van [symfony/dotenv](https://symfony.com/doc/current/components/dotenv.html) te lezen.

Voor deze stap de documentatie van [Symfony](https://symfony.com/doc/current) behulpzaam. De setup van de stek doet alle configuratie met YAML (in Symfony kun je kiezen tussen PHP, XML en YAML).

## Handle

![Request verwerken](../request/03_request_handle.png)

Het verwerken van een request wordt door een Controller gedaan. Hier komen we in het domein van onze eigen code. Iedere request komt aan bij een functie in een controller. Welke functie dit precies is is gedefineerd in de .yaml bestanden in `config/routes`. Iedere route in deze yaml bestanden correspondeerd met een functie in een controller.

Een controller functie returned een `Response` of een `ToResponse` die in de volgende stap naar de gebruiker verzonden kan worden. Kijk in [Routes](../backend/routes.md) om te lezen hoe je een nieuwe route toevoegt.

## Send

![Response naar gebruiker sturen](../request/04_request_send.png)

In deze stap wordt de Response die in de vorige stap geprepareerd is naar de gebruiker verzonden. In deze stap worden ook nog templates gerenderd.
