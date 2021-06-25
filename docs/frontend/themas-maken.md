---
layout: default
parent: Frontend
nav_order: 1
title: Themas
---

# Themas maken of bewerken
Het aanmaken van een nieuw thema voor de stek is aardig simpel, met het volgende stappenplan kun je best ver komen.

## Een nieuw thema maken
1. Maak een stylesheet aan in `assets/sass/thema` met de naam van je thema en extensie `.scss`.
2. Voeg de stylesheet toe aan de webpack configuratie in `webpack.config.js`, kijk hoe de andere thema's zijn toegevoegd.
3. Voeg het thema toe aan de lijst met themas in `config/instellingen/lid_instelling.yaml` onder `layout` > `opmaak`. Gebruik hier dezelfde naam als gekozen in de vorige stappen.

Bij het maken van een nieuw thema is het makkelijk om de inhoud van een bestaand thema te kopieren en deze aan te passen.

## Een bestaand thema aanpassen

In de map `assets/sass/thema` staan alle verschillende beschikbare thema's. Hier kun je in aanpassingen in maken, bijvoorbeeld als er een nieuw owee thema gemaakt moet worden.

## Structuur van een thema
Een thema scss bestand heeft drie onderdelen.

1. Het definieren van variabelen. In `assets/scss/_defaults.scss` staan een boel variabelen gedefinieerd. Deze zijn allemaal overschrijfbaar door ze in je eigen bestand te definieren. Bootstrap heeft ook nog eens een boel variabelen die je kan overschrijven. Zie hier voor de [documentatie van Bootstrap](https://getbootstrap.com/docs/4.0/getting-started/theming/).
1. De regel `@import "../base";`. Hier wordt alle stek scss ingeladen in jouw thema. Dit moet gebeuren na het definieren van de variabelen en voor de volgende stap. In sommige thema's staat hier `@import "./donker";` omdat deze thema's het donkere thema uitbreiden, dit is handig als je themakleuren ook donker zijn. Het donkere thema zorgt ervoor dat het contrast blijft kloppen bij donkere achtergrondkleuren.
1. Extra css. Als het echt niet mogelijk is om met variabelen je thema te maken en je moet ergens een kleine verandering maken, dan kun je nog extra css toevoegen. Doe dit wel spaarzaam, want dit is lastiger te onderhouden.

