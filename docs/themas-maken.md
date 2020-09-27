# Themas maken
Het aanmaken van een nieuw thema voor de stek is aardig simpel, met het volgende stappenplan kun je best ver komen.

## Opzetten
1. Maak een stylesheet aan in `assets/sass/thema` met de naam van je thema en extensie `.scss`.
2. Voeg de stylesheet toe aan de webpack configuratie in `webpack.config.js`, kijk hoe de andere thema's zijn toegevoegd.
3. Voeg het thema toe aan de lijst met themas in `config/instellingen/lid_instelling.yaml` onder `layout` > `opmaak`. Gebruik hier dezelfde naam als gekozen in de vorige stappen.

## Het maken van je thema
Het makkelijkst is om je thema te basseren op een al bestaand thema. Het sineregno thema is hier bijvoorbeeld een goede start voor. Kopieer de inhoud van dit thema naar je eigen bestand.

Een thema scss bestand heeft drie onderdelen.

1. Het definieren van variabelen. In `assets/scss/_defaults.scss` staan een boel variabelen gedefinieerd. Deze zijn allemaal overschrijfbaar door ze in je eigen bestand te definieren. Bootstrap heeft ook nog eens een boel variabelen die je kan overschrijven. Zie hier voor de [documentatie van Bootstrap](https://getbootstrap.com/docs/4.0/getting-started/theming/).
1. De regel `@import "../base";`. Hier wordt alle stek scss ingeladen in jouw thema. Dit moet gebeuren na het definieren van de variabelen en voor de volgende stap.
1. Extra css. Als het echt niet mogelijk is om met variabelen je thema te maken en je moet ergens een kleine verandering maken, dan kun je nog extra css toevoegen. Doe dit wel spaarzaam, want dit is lastiger te onderhouden.

