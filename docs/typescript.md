# JavaScript

De JavaScript van de stek wordt door [babel](https://babeljs.io/) getrokken voordat de eindegbruiker het ziet. Hierdoor is het mogelijk om je code in es6 te schrijven. Het is ook mogelijk om code in TypeScript te schrijven.


## Javascript voor specifieke routes
Het is nu mogelijk om javascript voor specifieke routes uit te voeren. De javascript voor deze routes wordt los opgehaald van de server. De code in de bestanden achter routes wordt in principe voor page-load uitgevoerd, dus het kan handig zijn om nog een `document.ready` er in te gooien. 
```javascript
// resources/assets/js/router.js

import {route} from './util';

// route(pathPrefix, cb);
route('/instellingen', () => import('./instellingen'));
route('/eetplan', () => import('./eetplan'));
```
Ik weet nog niet zeker of dit de manier is waarop het blijft werken. Voor het lidinstellingen overzicht had ik nog geen zin om een generieke oplossing te verzinnen (die er misschien wel een keer moet komen, maar misschien niet nodig is), daarom had ik iets van gebasseerd op route laden nodig. 

Als je op een andere plek `import(module)` gebruikt wordt ook een los bestand gemaakt voor de te importeren code (zolang het niet al op een andere plek geimport wordt).  Hier is `import` een synoniem van `require`, maar de laatste is _eigenlijk_ commonJS terwijl de eerste Harmony is (wat we prefereren).

Voor nog meer geavanceerd gebruik kun je ook het volgende doen.
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
Want `import()` geeft een promise terug met de module die geimport wordt. Probeer dit alleen niet te pas en te onpas te gebruiken, want het kan zo uit de hand lopen.