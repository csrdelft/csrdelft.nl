# Welkom op de C.S.R. Delft Laravel Stek

Op dit moment draait Laravel vóór de oude stek. Alle requests die 
Laravel niet kan afhandelen worden doorgespeeld naar de oude stek.

## Geimplementeerd

* Javascript & Css aanbieden
* CSRF tokens genereren en controleren
* Account acties
* Routing
* Sessies

## Javascript

Javascript wordt door webpack verzameld en omgezet naar es5 zodat 
alle moderne browsers het snappen. Dit wordt overzien door `laravel-mix`
dat er voor zorgt dat we geen eigen webpack configuratie hoeven te hebben
(nog niet). In `webpack.mix.js` staat deze configuratie.

### CommonJS `require`

We gebruiken de commonJS lader van webpack in de javascript, dit betekend dat
je op elk moment in je code `require` kan aanroepen met een bestand. Webpack zorgt
ervoor dat alle benodigde javascript bij elkaar gestopt wordt.

Het is wel aanbevolen om `require` alleen aan het begin van een bestand te gebruiken,
anders kan het nogal een zooi worden.

### Globale `window.<functie>`

Om het gebruik van webpack mogelijk te maken terwijl er nog een boel oude code is
wordt er op plekken waar gebruik werd gemaakt van impliciete globale functies nu
gebruik gemaakt van expliciete globale functies.

Als een functie beschikbaar moet zijn in een template, gebruik dan de volgende code. 
Zet er ook bij waar de functie wordt aangeroepen, want inspections werken niet met
globale functies die in templates gebruikt worden.

```javascript
/**
* @see templates/forum/post_lijst.tpl
*/
window.forumBewerken = function(postId) {
    ...
}
```

### Vendor, App & Manifest

`laravel-mix` splitst de gegenereerde js op in drie bestanden `manifest.js`,
`vendor.js` en `app.js`. Het manifest bestand bevat alleen de webpack lader,
vendor bevat externe libraries (die dat ondersteunen, zie `webpack.mix.js`), 
app bevat alle door ons geschreven code. In principe hoeft dus alleen app
geupdate te worden.

### Javascript bootstrap

Omdat er nog wat oude, brakke javascript code in de stek zit wordt in `bootstrap.js`
o.a. het `window.jQuery` object gebootstrapped (in elkaar geklust met plugins en al).
Dit zorgt ervoor dat libs en code die commonJS niet snappen (en dus gaan proberen om 
hun dependencies uit globals te halen) ook gewoon kunnen werken. Hier wordt ook 
de CSRF token aangeboden aan de ajax libs.

## Stylesheets

Voor stylesheets wordt SCSS gebruikt, wat eigenlijk SASS met brackets is. Deze worden ook in
`webpack.mix.js` opgegeven om gecompileerd te worden naar CSS. Met CSS is het wel makkelijk
om heel veel losse bestanden te hebben, dus dat wordt ook gedaan.

Alle referenties die je in stylesheets doet naar _relatieve_ urls, worden meegekopieerd 
naar `public` en de url wordt absoluut gemaakt.

## Mappenstructuur

* `app`
  * Bevat nagenoeg alle PHP code.
* `bin`
  * (Legacy) Bevat cron scripts en cli utility scripts.
* `bootstrap`
  * Creëert een applicatie met onze defaults. 
* `conf`
  * (Legacy) Bevat configuratie voor Docker.
* `config`
  * Bevat configuratie voor alle losse modules van Laravel en de stek.
* `data`
  * (Legacy) Bevat gecompileerde smarty.
* `database`
  * Bevat migraties.
* `db`
  * (Legacy) Bevat migraties.
* `docs`
  * Enkele flowcharts van bepaalde stek onderdelen.
* `etc`
  * (Legacy) Bevat configuratie voor lose modules.
* `htdocs`
  * (Legacy) Gaat verdwijnen
* `lib`
  * (Legacy) Bevat alle legacy PHP code.
* `node_modules`
  * Bevat alle externe javascript & css dependencies.
* `public`
  * Is `/` op de server en bevat statische bestanden. Wordt gevuld vanuit `resources`
* `resources`
  * Bevat alle javascript, css, afbeeldingen, vertalingen en Blade views.
* `routes`
  * Bevat routes voor verschillende onderdelen van de stek.
* `sessie`
  * (Legacy) Bevat sessie data.
* `sql`
  * (Heel Legacy) Bevat oude sql scripts.
* `storage`
  * Bevat sessie data, gecompileerde Blade views en logs.
* `tests`
  * Bevat tests.
* `vendor`
  * Bevat alle externe PHP code.