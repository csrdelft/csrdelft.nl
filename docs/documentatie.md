---
title: Documentatie
nav_order: 5
---

# Documentatie

De documentatie van de webstek (die je nu aan het lezen bent) is te vinden op [documentatie.csrdelft.nl](https://documentatie.csrdelft.nl) en de [/docs](https://github.com/csrdelft/csrdelft.nl/tree/master/docs) map in de repository.

Voel je vrij om bestanden in de docs map te veranderen en naar `master` te pushen, de stek zelf heeft geen last van.

In principe werkt standaard markdown gewoon in de documentatie, dus het voorbeeld wat je in PhpStorm kan zien ziet er ongeveer hetzelfde uit als de versie op de documentatie stek.

## Thema

Voor de documentatie stek wordt het [Just the Docs](https://pmarsceill.github.io/just-the-docs/) jekyll thema gebruikt. Zie hun documentatie voor meer info en handige dingen die je kan gebruiken.

In de documentatie van Just the Docs wordt ook uitgelegd hoe de [front matter](https://jekyllrb.com/docs/front-matter/) van de pagina werkt

## Lokaal testen

Als je wil sleutelen aan hoe de documentatie stek eruit ziet zonder dat je iedere keer moet pushen kun je de documentatie stek ook lokaal installeren. Dan heb je een Ruby installatie met `jekyll` en `bundler` geinstalleerd. Voer daarna de volgende commando's uit in de /docs map:

_Installeren van de hele Ruby stack op Windows is best een karwei_

```bash
# Installeer de boel
bundle install

# Start jekyll
bundle exec jekyll serve
```

Hierna kun je naar http://localhost:4000 gaan om de documentatie stek te bekijken.
