---
layout: default
title: Deploy
nav_order: 3
has_children: true
---

# Deploy

Een nieuwe versie van de stek op Syrinx deployen is erg makkelijk. Het enige wat je van tevoren nodig hebt is SSH toegang tot Syrinx. Waarschijnlijk is er wel een commissiegenoot die je daarmee verder kan helpen.

_Alle commando's worden vanuit de hoofdmap uitgevoerd_

## Voorafgaand

Voordat een deploy gedaan kan worden moet deze eerst klaargezet worden. De [`csrdelft/productie`](https://github.com/csrdelft/productie) repository wordt gebruikt om de laatste versie van alle code van de stek klaar te zetten. Deze kan dan op de server worden gedownload. De [CI](https://github.com/csrdelft/csrdelft.nl/actions/workflows/ci.yml) GitHub Action wordt hiervoor gebruikt, deze wordt uitgevoerd voor iedere nieuwe commit op master en zorgt ervoor dat de code naar `csrdelft/productie` wordt gepushed compleet op zichzelf staat en geen extra verwerking meer nodig heeft.

Kijk op de [GitHub Actions](./deploy/ci.md) pagina voor meer informatie over deze actions.

## Normale deploy

Zodra je bent ingelogd op Syrinx met SSH kun je het volgende commando uitvoeren om een nieuwe versie van de stek neer te zetten.

```bash
composer update-prod
```

Dit commando doet de volgende dingen (kijk in `composer.json` voor de precieze commandos):

1. Controleer of de `master` branch actief is en of er geen veranderingen open staan.
1. Download de laatste versie van de broncode (maar vervang de oude nog niet)
1. Zet de stek in onderhoudsmodus
1. Update de broncode van de stek
1. Lees de `.env` / `.env.local` / etc. bestanden uit en dump ze naar `.env.local.php`, dit zorgt ervoor dat de komende keren dat de env wordt opgevraagd het een stuk sneller is.
1. Run Doctrine migraties
1. Verwijder de Symfony cache
1. Flush de memcached cache
1. Haal de stek uit onderhoudsmodus
1. Warm de cache op.

## Snelle deploy

Als je een hele kleine verandering hebt gemaakt, zoals een verandering in de stylesheets of in een php script kan een snelle deploy mogelijk zijn. Met een snelle deploy hoeft de stek niet in onderhoudsmodus en merkt dus niemand dat er een deploy is geweest. Pas hier wel heel erg mee op, want als je toch de cache had moeten legen kan dit voor een boel errors in Slack zorgen.

Voor een snelle deploy voer je het volgende commando uit:

```bash
git pull
```

## Onderhoudsmodus

Onderhoudsmodus is handig bij updaten, want nu worden alle bezoekers naar de onderhoudspagina gestuurd en kun je eventjes vrij je dingen doen.

Onderhoudsmodus staat aan als het bestand `.onderhoud` bestaat en zorgt ervoor dat `htdocs/index.php` vroegtijdig afbreekt. Andere scripts werken wel gewoon.

```bash
# Onderhoudsmodus aan:
touch .onderhoud

# Onderhoudsmodus uit:
rm .onderhoud
```
