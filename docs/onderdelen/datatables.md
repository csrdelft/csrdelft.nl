---
layout: default
parent: Onderdelen
nav_order: 1
title: DataTables
---

# DataTables

In de stek worden op allerlei plekken datatables gebruikt, datatables is een jQuery plugin en is heel erg krachtig.

Er is een php wrapper voor datatables gebouwd om deze met php te kunnen op bouwen met behulp van het framework.

Zie `initDataTable` in `datatable/api.ts` voor de initializatie code van DataTables. Deze ontvangt een settings object vanuit de php, dit settingsobject wordt gemaakt in de [`DataTable`](https://github.com/csrdelft/csrdelft.nl/blob/master/lib/view/datatable/DataTable.php) klasse. De datatables api is heel erg declaratief waardoor dit op een elegante manier kan gebeuren.

## Werking

In een notendop werken datatabellen op de volgende manier:

- De `DataTable` (oud) óf `DataTableBuilder` (nieuw) genereerd een JSON waarde met configuratie voor datatabellen, zie [datatables.net](https://datatables.net/reference/option/) voor alle mogelijke opties.
- Er wordt een html tag gemaakt met een specifieke klasse `ctx-datatable` en een `data-settings` attribuut die de gegeneerde opties json bevat.
- `initDataTable` (`assets/js/datatable/api.ts`) wordt aangeroepen voor iedere `ctx-datatable`, deze functie initialiseert een nieuwe datatabel
- Datatabellen zijn op verschillende plekken uitgebreid
  - extensie `dataTables.childRow.ts` om een regel uit te klappen met nog wat inhoud (nog een datatabel bijvoorbeeld)
  - extensie `dataTables.columnGroup.ts` om te kunnen groeperen op een specifieke kolom
  - extensie `dataTables.rowButton.ts` om knoppen op een datatabel regel te kunnen hebben
  - custom renders in `render.ts` zie [Renders](#renders)
  - custom knoppen in `buttons.ts` zie [Knoppen](#knoppen)

## API

Het bestand [`api.ts`](https://github.com/csrdelft/csrdelft.nl/blob/master/assets/js/datatable/api.ts) is losgetrokken omdat anders de externe stek op datatables zou leunen terwijl dat niet nodig is. Er zijn een aantal functies in de knoppen code die datatables gebruikt en die worden ook op de externe stek geladen, maar de externe stek heeft geen datatables.

## Knoppen

Boven een datatable staat bijna altijd een rij met knoppen, het aanmaken van een knop is heel makkelijk. Ze zijn geïmplementeerd in [`buttons.ts`](https://github.com/csrdelft/csrdelft.nl/blob/master/assets/js/datatable/buttons.ts)

```php
// In een DataTable class' __construct
$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/maaltijden/beheer/verwijder', 'Verwijderen', 'Maaltijd verwijderen', 'cross'));
```

De volgende opties zijn er om knoppen eigenschappen te geven.

- `default`
  - [`CsrDelft\view\formulier\datatable\knop\DatatableKnop`](/csrdelft/csrdelft.nl/blob/master/lib/view/formulier/datatable/knop/DataTableKnop.php)
  - standaard ingesteld
  - kijkt naar de multipliciteit die gegeven is, dit slaat op de selectie, `== 1` betekent één geselecteerde item. `> 1` betekend meer dan een geselecteerde item.
  - Zorgt ervoor dat de knop zich gedraagt als een `ajax_knop`, de default actie van knoppen vóór #206
  - Kan een argument van een row meegeven in de url. Bijvoorbeeld `href=/maaltijden/beheer/:maaltijd_id` verwijst naar de id in de geselecteerde row. Dit werkt alleen (stabiel) bij multipliciteit `== 1`, gebruik deze multipliciteit om te garanderen dat er iets gebeurt. Als er meer dan 1 geselecteerd item is wordt er geen poging gedaan de lege plekken in te vullen.

![2017-02-04_18-36-52](https://cloud.githubusercontent.com/assets/589651/22620297/f2aa4924-eb08-11e6-9cf6-2b7a28456c26.gif)

- `popup`
  - [`CsrDelft\view\formulier\datatable\knop\PopupDataTableKnop`](/csrdelft/csrdelft.nl/blob/master/lib/view/formulier/datatable/knop/PopupDataTableKnop.php)
  - Alles van `default`
  - Opent een nieuw venster met de href die megegeven is aan de knop
- `url`
  - [`CsrDelft\view\formulier\datatable\knop\UrlDataTableKnop`](/csrdelft/csrdelft.nl/blob/master/lib/view/formulier/datatable/knop/UrlDataTableKnop.php)
  - Alles van `default`
  - Zelfde als popup, maar dan in hetzelfde venster
- `sourceChange`
  - [`CsrDelft\view\formulier\datatable\knop\SourceChangeDataTableKnop`](/csrdelft/csrdelft.nl/blob/master/lib/view/formulier/datatable/knop/SourceChangeDataTableKnop.php)
  - Veranderd de bron van een datatable naar de meegegeven href. Heeft een ingedrukte staat als de button actief is.

![2017-02-04_18-35-46](https://cloud.githubusercontent.com/assets/589651/22620292/cfee0d12-eb08-11e6-8408-633e8e2f09ac.gif)

- `confirm`
  - [`CsrDelft\view\formulier\datatable\knop\ConfirmDataTableKnop`](/csrdelft/csrdelft.nl/blob/master/lib/view/formulier/datatable/knop/ConfirmDataTableKnop.php)
  - Laat een gebruiker op 'Weet u dit zeker?' klikken

![2017-01-18_16-51-06](https://cloud.githubusercontent.com/assets/589651/22620276/7b994830-eb08-11e6-8ac2-6d231dd84716.gif)

- `defaultCollection`
  - [`CsrDelft\view\formulier\datatable\knop\CollectionDataTableKnop`](/csrdelft/csrdelft.nl/blob/master/lib/view/formulier/datatable/knop/CollectionDataTableKnop.php)
  - Dit is [`collection`](https://datatables.net/extensions/buttons/examples/initialisation/collections.html) van datatables, maar dan met de multipliciteit van default in acht genomen.
  - Gebruik deze in plaats van `collection`.

![2017-02-04_18-38-22](https://cloud.githubusercontent.com/assets/589651/22620306/2a9f66fc-eb09-11e6-8965-371d704152f2.gif)

## Renders

- default, zorgt ervoor dat de juiste weergave van de data wordt gekozen (sort, export, default)
- bedrag, format een bedrag van 0000 naar €0,00
- check, laat een vinkje of kruisje zien voor een boolean
- aanmeldFilter, custom html voor maaltijden
- aanmeldingen, custom voor maaltijden
- totaalPrijs, custom voor maaltijden
- date, format een datum
- time, format een tijd
- datetime, format een datum en tijd
- timeago, format als tijd sinds nu (met js)
- filesize, format een bestandsgrootte in bytes.
