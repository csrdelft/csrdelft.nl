---
layout: default
parent: Onderdelen
nav_order: 1
title: Wiki (oud)
---

# Wiki (oud)

De oude wiki is een [submodule](../submodule.md) en bevat een [DokuWiki](https://www.dokuwiki.org/dokuwiki) installatie met een aantal eigen plugins.

## Lokaal gebruiken

De makkelijkste aanpak hier is om de versie die nu op Syrinx staat naar je lokale pc te downloaden. Je kan hier het makkelijkst scp voor gebruiken, dat komt met ssh.

In de `htdocs/wiki` map:

```bash
scp -r csrdelft@csrdelft.nl:/srv/www/csrdelft.nl/htdocs/wiki/* .
```

Dit download alles en omdat dokuwiki op het bestandssysteem werkt (en niet met een database) zou alle data nu ook moeten kloppen.

Zet in het bestand `htdocs/wikki/conf/local.php` het veld `baseurl` goed, anders wordt je naar de productie wiki doorgelinkt.

## Updaten van de wiki en extensies

Via de beheerinterface van de wiki kun je de wiki en extensies updaten. Doe dit niet op Syrinx, vanaf Syrinx kun je niet makkelijk veranderingen weer online zetten. Doe dit dus op je lokale installatie en push de veranderingen naar Github, dan worden ze automatisch goed gezet bij deployen.

## Eigen extensies

Er zijn een aantal extensies die specifiek zijn voor de stek, ze regelen documenten, groepen etc.

* `authcsr`
  * Zorgt ervoor dat inloggen via het stek systeem gaat.
* `csrgroepgeschiedenis`
  * Een linkje naar de groepen pagina
* `csrlink`
  * Een linkje naar een boek/document/groep/lid

## Rechten

Dokuwiki kan allerlei dingen met rechten, maar niemand weet hoe dit precies werkt. Heb jij het uitgezocht? Pas dan dit document aan.

