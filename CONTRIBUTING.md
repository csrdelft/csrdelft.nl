# Bijdragen aan de stek

Kijk op https://documentatie.csrdelft.nl of in [docs/README.md](docs/README.md) voor een uitleg van de installatie van de stek.

## Development

### Editorconfig
Zorg ervoor dat je editor zo is ingesteld dat de code style (indent/newline types) goed staat ingesteld. Check .editorconfig voor de huidige instellingen.

In PhpStorm kun je de Editorconfig plugin installeren om automatisch de instellingen goed te zetten voor dit project.

### Branches & PR
We hebben enkele branches. Niet heel bijzonder.

- `master` -  live stek, niet stukmaken! Taart als dat wel gebeurd.
- `#issue - naam`: graag issue nummer (van github) vermelden indien mogelijk.
- `naam`: overig

We werken met PRs voor de meeste gevallen. Probeer je code door ten minste 1 persoon te laten reviewen.
Diegene mag hem mergen en zal er vervolgens voor zorgen dat de live stek wordt geupdate.

### Issues
#### Type
Spreekt redelijk voorzich. De kleur is een pastelkleur. De specifieke kleur per label is willekeurig.
`type:enhancement` - Verbetering voor de huidige code base / architectuur. Dit kan bijvoorbeeld een performance verbetering zijn maar ook een refactor.
`type:design` - Puur gericht op uiterlijk.
`type:feature` - Nieuwe toevoeging ten opzichte van huidige code base.
`type:bug`
`type:security`
`type:task`
`type:question`

#### Prioriteit
Het inschatten van dit label kan lastig zijn. Hieronder enkele richtlijnen. De kleuren voor deze labels zijn rood, oranje en geel.
`prio:high` - Moet veranderd worden om de stek draaiende te kunnen houden. Tevens problemen die het gebruik van de stek regelmatig verstoren.
`prio:normal` - Zaken die van waarde zijn maar de huidige stek niet ontregelen als ze niet gefixt worden. Denk hierbij aan nieuwe refactors.
`prio:low` - Veelal issues die vooralsnog genegeerd kunnen worden. Bevatten vaak mooie ideeen, maar daar is nu geen tijd voor / behoefte aan.

#### Component
Het component label is altijd zwart en beschrijft het onderdeel van de stek waar dit over gaat. Deze wordt, in tegenstelling tot de vorige 2, niet consistent gebruikt. Dit zou in een later stadium nog gedaan kunnen worden. Mogelijk zou dit ook met milestones kunnen.
`component:forum`
`component:soccie`
...

### CmsPaginas
De volgende CMS pagina's zijn gehardcoded in de stek, zorg dat deze in de `cms_paginas` tabel aanwezig zijn.

* De lege CMS pagina, alle velden leeg
* `thuis`
* `accountaanvragen`
* `mobiel`
* `UitlegACL`
* `fotostoevoegen`
* `403`
