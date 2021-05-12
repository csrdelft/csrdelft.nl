---
layout: default
parent: Introductie
nav_order: 2
title: Frontend Build
---

# Frontend Build

De frontend bestaat uit [Typescript](../frontend/typescript.md) en [SCSS](../frontend/styles.md). En wordt met behulp van webpack getranspileerd naar Javascript en CSS.

## Build uitvoeren

Als je een build uitvoert worden de gegenereerde bestanden in de map `htdocs/dist` geplaatst, hier zijn ze ook bereikbaar vanuit de webbrowser.

### De development build uitvoeren

Om de frontend build te starten voor ontwikkelen aan de stek gebruik je het volgende commando:

```bash
yarn dev
```

Dit maakt een *development* build van de stek. In de development build is de gegenereerde Javascript en CSS mooi geformatteerd, dus als je er even in wil kijken is het nog steeds goed te begrijpen.

### De productie build uitvoeren

Om de frontend build te starten voor productie gebruik je het volgende commando:

```bash
yarn prod
```

Dit maakt een *productie* build van de stek. In de productie build is de gegenereerde Javascript en CSS geminified (variabel namen worden ingekort, spaties en tabs worden verwijderd), dit scheelt in de grootte van bestanden. Daarnaast krijgen de bestanden een hash in hun bestandsnaam zodat er in productie gezegd kan worden dat een bestand nooit zal veranderen, want als een bestand veranderd krijgt deze een nieuwe naam.

## Webpack

[Webpack](https://webpack.js.org/) is een hele handige tool voor het builden van frontend dingen. In het bestand `webpack.config.js` is gedefineerd hoe de build precies werkt.

De bestanden onder `entry` zijn interessant, dit zijn de aanspreekpunten van de Typescript/Scss.

## De Manifest

Webpack genereerd veel bestanden. Het manifest.json (`htdocs/dist/manifest.json`) bestand wordt aangemaakt om bij te houden welke bestanden er allemaal gemaakt zijn en waar ze te vinden zijn. Met de methodes `js_asset` en `css_asset` wordt in dit bestand gekeken en worden de juiste bestanden als html terug gegeven.

## Dependencies

Er wordt natuurlijk ook voortgebouwd op bestaande Javascript code, bijvoorbeeld in [Vue](../frontend/vue.md) en [DataTables](../onderdelen/datatables.md). In het bestand `package.json` staat een overzicht met alle javascript dependencies. Deze dependencies zijn opgesplitst in twee groupen, `dependencies` en `devDependencies`. Dependencies zijn dependencies die in de Typescript code gebruikt worden. Dev dependencies worden gebruikt door webpack om de Typescript om te vormen naar Javascript (en de SCSS naar CSS).
