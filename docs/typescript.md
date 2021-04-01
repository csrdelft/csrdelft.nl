# Typescript

De Typescript van de stek wordt door de TypeScript compiler getrokken voordat de eindegbruiker het ziet. Hierdoor is het mogelijk om je code in es6 te schrijven en typescript te gebruiken. Kijk in [Frontend](frontend.md) voor meer info over deze stap.

Het bestand `app.ts` is het aanspreekpunt van de Typescript, vanaf hier wordt alles ingeladen. Er zijn nog een aantal andere losse bestanden die worden gebruikt om de javascript op te splitsen, kijk in `webpack.config.js` voor alle javascript en sass bestanden die aanspreekpunt zijn.

## Typescript & PHP laten samenwerken

Een voorbeeld waar PHP een stuk javascript opstart is te zien in `GroepStatistiekView`. Hier hebben de verschillende grafieken een specifieke klassenaam en een data attribuut met alle informatie die interessant is voor de grafiek.

In het bestand `grafiek.ts` wordt er een handler aan `ctx` toegevoegd als volgt:

```typescript
ctx.addHandlers({
	'.ctx-graph-pie': initPie,
});
```

Hier wordt een handler gecreeerd voor de selector `.ctx-graph-pie` met de handler `initPie`. Dit zorgt ervoor dat iedere keer als de 'context' geladen wordt en een node met klasse `ctx-graph-pie` wordt gevonden de `initPie` functie wordt aangeroepen met als argument die specifieke node.

De 'context' wordt voor ieder nieuw blokje html dat wordt ingeladen uitgevoerd. Bijvoorbeeld als er een modal wordt geladen, dan wordt de context geinitializeerd en wordt voor alle handlers in de context gecontroleerd of er iets te laden valt.

Met deze aanpak hoef je in je templates geen Javascript code te schrijven. Dit kan op de lange termijn er voor zorgen dat we CSP (Content Security Policy) kunnen instellen waardoor de stek nog een stuk veiliger wordt.

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
Want `import()` geeft een promise terug met de module die geimport wordt. Probeer dit alleen niet te pas en te onpas te gebruiken, want het kan zo uit de hand lopen.
