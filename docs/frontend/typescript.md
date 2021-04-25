---
layout: default
parent: Frontend
nav_order: 1
title: Typescript
---

# Typescript

De Typescript van de stek wordt door de TypeScript compiler getrokken voordat de eindegbruiker het ziet. Hierdoor is het mogelijk om je code in es6 te schrijven en typescript te gebruiken. Kijk in [Frontend](../introductie/frontend.md) voor meer info over deze stap.

Het bestand `app.ts` is het aanspreekpunt van de Typescript, vanaf hier wordt alles ingeladen. Er zijn nog een aantal andere losse bestanden die worden gebruikt om de javascript op te splitsen, kijk in `webpack.config.js` voor alle javascript en sass bestanden die aanspreekpunt zijn.

## Typescript & PHP laten samenwerken

TypeScript en PHP leven allebei in hun eigen wereld. Om deze twee systemen goed met elkaar samen te laten werken is de volgende oplossing verzonnen.

De PHP code geeft HTML terug met specifieke klassen die door de TypeScript code worden opgepikt en worden geinitialiseerd. Bijvoorbeeld de volgende html, hier wordt een element met de klasse `ctx-graph-pie` gemaakt.

```html
<div class="ctx-graph-pie" data-data="{$verticale}"></div>
```

In de TypeScript code wordt een nieuwe handler aan de 'context' `ctx` gehangen. `ctx` is een singleton waar allerlei handlers aan gehangen kunnen worden, op basis van een selector.

```ts
import ctx from './ctx'
ctx.addHandlers({
	'.ctx-graph-pie': initPie,
});
```

Iedere keer als de pagina wordt geladen of als er een stuk html van de server wordt geladen wordt de context aangeroepen en deze roept alle handlers aan die iets selecteren in de nieuwe html. Zo kun je makkelijk PHP aan TypeScript knopen, zonder dat je javascript code in php hoeft te schrijven. Javascript code on-the-fly genereren met PHP is vaak een slecht idee en is iets wat we dus proberen te voorkomen.

Vue wordt ook geinitialiseerd met de context, zie hiervoor de [Vue](./vue.md) pagina.

## Javascript voor specifieke routes / Dynamic Import

Het is mogelijk om javascript voor specifieke routes uit te voeren. De javascript voor deze routes wordt los opgehaald van de server. De code in de bestanden achter routes wordt in principe voor page-load uitgevoerd, dus het kan handig zijn om nog een `document.ready` er in te gooien.

Als je deze dynamic imports gebruikt **moet** je een `webpackChunkName` opgeven anders breekt de javascript omdat de chunk dan geen naam heeft en niet terug kan worden gevonden. Dit is deels een bug in webpack.

Meer info is te vinden op de [Code Splitting](https://webpack.js.org/guides/code-splitting/#dynamic-imports) pagina in de webpack docs.

```javascript
// assets/js/router.js

import {route} from './util';

// route(pathPrefix, cb);
route('/instellingen', () => import(/* webpackChunkName: "instellingen" */'./instellingen'));
route('/eetplan', () => import(/* webpackChunkName: "eetplan" */'./eetplan'));
```
Ik weet nog niet zeker of dit de manier is waarop het blijft werken. Voor het lidinstellingen overzicht had ik nog geen zin om een generieke oplossing te verzinnen (die er misschien wel een keer moet komen, maar misschien niet nodig is), daarom had ik iets van gebasseerd op route laden nodig.

Als je op een andere plek `import(/* webpackChunkName: "module" */module)` gebruikt wordt ook een los bestand gemaakt voor de te importeren code (zolang het niet al op een andere plek geimport wordt).  Hier is `import` een synoniem van `require`, maar de laatste is _eigenlijk_ commonJS terwijl de eerste Harmony is (wat we prefereren).

Voor nog meer geavanceerd gebruik kun je ook het volgende doen.
```javascript
// main.js
import(/* webpackChunkName: "eetplan" */'./eetplan').then((module) => {
  console.log('Module is geladen!');
  module.initialiseerEetplan();
});

// eetplan.js
export function initialiseerEetplan() {
  console.log('Laad Eetplan');
}
```

Want `import()` geeft een promise terug met de module die geïmporteerd wordt. Probeer dit alleen niet te pas en te onpas te gebruiken, want het kan zo uit de hand lopen.
