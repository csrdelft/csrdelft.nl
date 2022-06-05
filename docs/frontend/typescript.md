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
import ctx from './ctx';
ctx.addHandlers({
	'.ctx-graph-pie': initPie,
});
```

Iedere keer als de pagina wordt geladen of als er een stuk html van de server wordt geladen wordt de context aangeroepen en deze roept alle handlers aan die iets selecteren in de nieuwe html. Zo kun je makkelijk PHP aan TypeScript knopen, zonder dat je javascript code in php hoeft te schrijven. Javascript code on-the-fly genereren met PHP is vaak een slecht idee en is iets wat we dus proberen te voorkomen.

Vue wordt ook geinitialiseerd met de context, zie hiervoor de [Vue](./vue.md) pagina.

## Javascript voor specifieke routes / Dynamic Import

Het is mogelijk om javascript voor specifieke routes of onder specifieke omstandigheden uit te voeren. De javascript wordt dan los opgehaald van de server. De code in de bestanden achter routes wordt in principe voor page-load uitgevoerd, dus het kan handig zijn om nog een `document.ready` er in te gooien.

Meer info is te vinden op de [Code Splitting](https://webpack.js.org/guides/code-splitting/#dynamic-imports) pagina in de webpack docs.

Voorbeelden van het gebruik van dynamisch laden in de stek:

- `router.ts`, om voor specifieke pagina's te laden.
- `context.ts`, waar bij het laden van een specifieke context ook de dependencies geladen worden, hier wordt dat gedaan omdat sommige onderdelen in de interne én externe stek geladen worden en sommige alleen op de interne stek.
- `fotoalbum/main.ts`, als de gebruiker is ingelogd wordt ook `with-tags.ts` geladen en als de gebruiker een beheerder is, wordt ook `with-admin-buttons.ts` geladen.

```javascript
// assets/js/router.ts

import { route } from './util';

// route(pathPrefix, cb);
route('/instellingen', () => import('./instellingen'));
route('/eetplan', () => import('./eetplan'));
```

Als je op een andere plek `import(module)` gebruikt wordt ook een los bestand gemaakt voor de te importeren code (zolang het niet al op een andere plek geimport wordt). Webpack probeert zo slim mogelijk de modules in stukjes te knippen.

Voor nog meer geavanceerd gebruik kun je ook het volgende doen. Om code uit te voeren nadat de module geladen is.

```javascript
// main.js
import('./eetplan').then((module) => {
	console.log('Module is geladen!');
	module.initialiseerEetplan();
});

// eetplan.js
export function initialiseerEetplan() {
	console.log('Laad Eetplan');
}
```

Want `import()` geeft een promise terug met de module die geïmporteerd wordt. In het veld `default` staat de default export van de module.
