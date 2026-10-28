# Lustrum XIII — Panorama

De pagina staat op `/panorama`, met dezelfde loginbeveiliging als de Dies-pagina’s. Alleen Puur natuur wordt gepubliceerd. Geen databasewijzigingen nodig voor de pagina.

## Opbouw en uitbreiden

`templates/lustrum13/index.html.twig` bevat de oorspronkelijke jaarposter, het huidige thema. `programma.html.twig` bevat de toegankelijke tekstversie van het programma. De stijl staat in `assets/scss/lustrum13/lustrum13.scss`.

Bij een volgende onthulling kan de huidige `.nature`-sectie naar een eigen include worden verplaatst. Voeg dan een navigatie met uitsluitend gepubliceerde momenten toe tussen de jaarposter en de thema-inhoud. Houd het huidige anker `#puur-natuur` bereikbaar. Bewaar toekomstige thema’s en data tot publicatie buiten de gerenderde HTML en JavaScript; ook geen verborgen teasers of gereserveerde tabs. `#programma` blijft het programma van het actuele moment.

## Bronmateriaal

[Drive / Lustrum XIII](https://drive.google.com/drive/folders/1OnmvhvCfCB69VB4_s9x5eHT68USUpNX8), map `6 - Vormgeving`:

- `Panorama / Lustrum XIII themaposter C.S.R. Delft.pdf`: volledige openingsposter en landschapuitsnede.
- `Lustrumweek 1 / SVG bestand posters voor de Pubcie 1-01.svg`: Puur natuur-poster, met sponsor.
- `Lustrumweek 1 / SVG bestand posters voor de Pubcie 2-02.svg`: programmaposter; tekst en tijdstippen hieruit letterlijk overgenomen. Ochtendactiviteiten hebben geen verzonnen aanvangstijd. Geen ongecontroleerde inschrijflinks toegevoegd.
- `HET KLEURENPALLET.jpeg`: officiële kleuren.

De posters zijn naar WebP omgezet, zonder nieuwe illustraties te genereren. Lokale bestaande lettertypen. De originele tekeningen en auteurscredits blijven behouden.

## Mogelijke uitbreidingen om met de commissie te bespreken

- Zoekplaat met echte verenigingsdetails, in overleg met de illustrator: bijvoorbeeld een verborgen das, bierpul of zingende eend. Klikbare gebouwen kunnen korte verenigingsverhalen openen.
- Panorama-postkantoor: een ingescande, handgeschreven ansichtkaart van deelnemers per activiteit, na toestemming en moderatie.
- Een wandelpaspoort: fysieke stempels tijdens activiteiten; digitaal alleen het overzicht en de route, geen locatievolging.
- Dagelijkse uitsnede uit een eigen verenigingsfoto: raad de plek, met onthulling de volgende dag. Vereist een door de commissie beheerde reeks foto’s.
- Een gezamenlijk panorama van ingezonden foto’s, na selectie door de commissie. Geen automatische openbare uploads.

Deze ideeën zijn geen aangekondigde programmaonderdelen en verschijnen niet op de website.

## Controle

Bouw met `yarn dev`. Controleer desktop, mobiel, toetsenbord en pagina zonder JavaScript. De historische Dies-pagina’s en hun stijlen blijven afzonderlijke webpack-entries.

## Verwijderde spellen

Het spellendashboard, de dagvraag, het beheer en alle spelroutes zijn verwijderd. De lustrumpagina bevat geen spel of verwijzingen daarnaartoe. Bestaande opslag en migraties blijven behouden om opgeslagen gegevens niet te wissen.
