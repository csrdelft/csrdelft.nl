# Themas maken
Het aanmaken van een nieuw thema voor de stek is aardig simpel, met het volgende stappenplan kun je best ver komen.

## Opzetten
1. Maak een stylesheet aan in `resources/assets/sass/thema` met de naam van je thema en extensie `.scss`.
2. Voeg de stylesheet toe aan de webpack configuratie in `webpack.config.js`, kijk hoe de andere thema's zijn toegevoegd.
3. Maak het thema selecteerbaar in `lib/model/LidInstellingenModel.class.php`, voeg m toe aan het lijstje bij `'opmaak'`

## Het maken van je thema
1. Kopieer de inhoud van `resources/assets/sass/_defaults.scss` naar je eigen `scss` bestand.
2. Plaats `@import "../base";` aan het einde van het thema.
2. De waardes die in `_defaults.scss` staan worden gebruikt als je niets insteld in je eigen thema. De waardes in `_defaults.scss` zijn alle mogelijke variabelen die er op dit moment zijn.
3. Verander de waardes van de variabelen, je kan nu `!default` weghalen, want je wil niet dat de waarde op een andere plek nog gezet kan worden.
4. Is er een waarde die je wil aanpassen waar geen variabele van is, maak dan een variabele aan en zet deze in `_defaults.scss`. Hierdoor blijven alle andere thema's werken en kun je zelf de waarde veranderen in je eigen thema.
5. Je kan in je thema ook extra css toevoegen, maar doe dit niet te veel. Hierdoor kan het een bende worden.
6. Alle waardes die je niet hebt aangepast kun je weghalen, zodat het netjes wordt en je geen `!default` meer in je thema hebt staan.
