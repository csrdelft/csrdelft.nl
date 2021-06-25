---
layout: default
parent: Backend
nav_order: 1
title: Permissies
---

# Permissies

Niet iedereen kan alles overal zien.

Er zijn twee vormen van permissies binnen de stek, gebasseerd op 'Mandatory access control (MAC)' en 'Discretionary access control (DAC)'. Op sommige plekken wordt een permissie gedefinieerd, zoals `P_DOCS_READ`, en aan een bepaald niveau worden permissies gehangen die daarvoor relevant zijn. Zo krijgt een lid (`R_LID`) de permissie `P_DOCS_READ` en het bestuur krijgt alles wat lid ook heeft, plus nog wat meer.

De andere vorm, DAC, is gebasseerd op eigenschappen van een lid, bijvoorbeeld welke commissie dit lid doet, welk lidjaar, welk geslacht, etc. Deze vorm wordt gebruikt als er specifiekere rechten nodig zijn.

Over het algemeen geldt: als iets in code gezet wordt, gebruik MAC. Als iets in instellingen staat, gebruik DAC, tenzij er een MAC benaming voor bestaat. Hier moet natuurlijk een goede inschatting gemaakt worden.

De volgende opties zijn mogelijk:
(Wanneer er meer opties worden opgeven, deze scheiden met komma's. Geen spaties gebruiken.)

update: Er zijn er nog veel meer, en het wordt via strings gedaan voor meer levels per module.

## Permissies

 * READ = Rechten om het onderdeel in te zien
 * POST = Rechten om iets toe te voegen
 * MOD  = Moderate rechten, dus verwijderen enzo

De rechten zijn cumulatief en octaal

Een paar voorbeelden (de namen en de toewijzing van permissies zijn inmiddels gewijzigd, zie code voor actuele staat)

 * `P_PUBLIC` - Iedereen op het Internet
 * `P_LOGGED_IN` - Leden-menu, eigen profiel raadplegen
 * `P_ADMIN` - Admin dingen algemeen...
 * `P_FORUM_READ` - Forum lezen
 * `P_FORUM_POST` - Berichten plaatsen op het forum en eigen berichten wijzigen
 * `P_FORUM_MOD` - Forum-moderator mag berichten van anderen wijzigen of verwijderen
 * `P_DOCS_READ` - Documenten-rubriek lezen
 * `P_DOCS_POST` - _Ongebruikt_
 * `P_DOCS_MOD` - Documenten verwijderen of plaatsen
 * etc, etc..
Sommige van deze permissies zijn zelfstandig. Sommige zijn cumulatief, zie code voor details.

Daarmee kunnen rollen gedefineerd worden, die elke een selectie van bovenstaande permissies krijgen:
 * `R_NOBODY` => P_NOBODY, P_FORUM_READ, P_AGENDA_READ
 * `R_ETER` => P_LOGGED_IN, P_MAAL_IK, P_MAAL_WIJ, P_PROFIEL_EDIT
 * `R_LID` => P_LOGGED_IN, P_OUDLEDEN_READ, P_FORUM_POST, P_DOCS_READ, P_LEDEN_READ, P_PROFIEL_EDIT, P_AGENDA_POST, P_MAAL_WIJ, P_MAIL_POST
 * `R_OUDLID` => P_LOGGED_IN, P_LEDEN_READ, P_OUDLEDEN_READ, P_PROFIEL_EDIT, P_FORUM_READ, P_MAIL_POST
 * `R_MODERATOR`  => P_ADMIN, P_FORUM_MOD, P_DOCS_MOD, P_LEDEN_MOD, P_OUDLEDEN_MOD, P_AGENDA_MOD, P_MAAL_MOD, P_MAIL_SEND, P_NEWS_MOD, P_BIEB_MOD

Sommige rollen zijn uitbreiding van andere rollen:
 * `R_PUBCIE` = R_MODERATOR
 * `R_MAALCIE` = R_LID, P_MAAL_MOD
 * `R_BESTUUR` = R_LID, P_LEDEN_MOD, P_OUDLEDEN_READ, P_NEWS_MOD, P_MAAL_MOD, P_MAIL_COMPOSE, P_AGENDA_MOD, P_FORUM_MOD, P_DOCS_MOD
 * `R_VAB` = R_BESTUUR, R_OUDLEDEN_MOD
 * `R_KNORRIE` = R_LID, R_MAAL_MOD

Kijk in `AccessService` voor de volledige implementatie van rechten.

## Andere manieren van toegang
Naast permissies zijn veel andere eenheden geschikt voor geven van toegang.

### Lidnummers
 * `4444` of `x101` lidnummers zoals te vinden in profiel

### groepen
 * `groep:kortegroepnaam` - Selecteert de ht groep . bijvoorbeeld: groep:AcqCie
 * `groep:groepsnummer` - bijv: groep:104

### verticalen
 * `verticale:letter`
   * Selecteert verticale:letter, waarvoor letter A t/m H mogelijk is. bijvoorbeeld: verticale:A
   * Selecteert nummer. Bijv. verticale:3
   * Selecteert naam. Bijv. verticale:Securis


# Uitleg van bitwise vergelijken
Een permissie bestaat uit een rij van toegangslevels die elk gekoppeld zijn aan een onderdeel. De toegangslevels zijn integer waardes, het onderdeel wordt bepaald door de positie van die integer waarde in de rij.

De integers in \_permissions kunnen octaal opgeslagen worden. Dan worden geprefixt met een nul: `0![0-7]+`.
http://php.net/manual/en/language.types.integer.php. Elke cijfer vertegenwoordigd dan een integer waarde. De hoogste mogelijke integere waarde van een octaal is 7. Een alternative manier van opslaan is als string. Dan vertegenwoordigt elke karakter een integer waarde, de hoogste mogelijke integere waarde ligt nu heel veel hoger.

De integers in \_permissions attribute worden bitwise vergeleken:
http://php.net/manual/en/language.operators.bitwise.php

Dit betekent dat elke digit van de integer wordt omgezet naar naar bits (bijv. 5 = 0101),
en deze bits worden bit voor bit in beide strings vergeleken.
(voorbeeld: 5 & 3 = 1, want in bits: 0101 & 0011 = 0001)

Voor een string wordt elk character omgezet in de ASCII waarde, en dat in bits.

De bitwaardes worden vervolgens per teken vergeleken door AND operator te gebruiken. Dit laat zien welke bits in de bitwaarde overeenkomen.

(disclaimer, de lijstjes hieronder zijn handmatig gegeneerd, dus bevat mss foutjes)
```
binair: 0001
1&0 = 0
1&1 = 1, (binair: 0001&0001 = 0001)
1&2 = 0, (binair: 0010&0001 = 0000)
1&3 = 1,
1&4 = 0,
1&5 = 1,
1&6 = 0
1&7 = 1,
1&8 = 0,
1&9 = 1
```
```
binair: 0010
2&0 = 0,
2&1 = 0, (binair: 0010&0001 = 0000)
2&2 = 2, (binair: 0010&0010 = 0010)
2&3 = 2, (binair: 0010&0011 = 0010)
2&4 = 0, (binair: 0010&0100 = 0000)
2&5 = 0,
2&6 = 2,
2&7 = 2,
2&8 = 0,
2&9 = 0
```
```
binair: 0011
3&0 = 0
3&1 = 1, (binair: 0011&0001 = 0001)
3&2 = 2,
3&3 = 3,
3&4 = 0
3&5 = 1
3&6 = 2
3&7 = 3
3&8 = 0
3&9 = 1
```
```
binair: 0100
4&0 = 0
4&1 = 0, (binair: 0100&0001 = 0000)
4&2 = 0,
4&3 = 0,
4&4 = 1
4&5 = 1
4&6 = 1
4&7 = 1
4&8 = 0
4&9 = 0
```
```
binair: 0101
5&0 = 0
5&1 = 1, (binair: 0101&0001 = 0001)
5&2 = 0,
5&3 = 1,
5&4 = 4
5&5 = 5
5&6 = 4
5&7 = 5
5&8 = 0
5&9 = 1
```
```
binair: 0110
6&0 = 0
6&1 = 0, (binair: 0100&0001 = 0000)
6&2 = 2,
6&3 = 2,
6&4 = 4
6&5 = 4
6&6 = 6
6&7 = 6
6&8 = 0
6&9 = 0
```
```
binair: 0111
7&0 = 0
7&1 = 1, (binair: 0111&0001 = 0001)
7&2 = 2,
7&3 = 3,
7&4 = 4
7&5 = 5
7&6 = 6
7&7 = 7
7&8 = 0
7&9 = 1
```
Als we met octalen werken, is dit onzin...:
```
binair: 1000
8&0 = 0
8&1 = 0, (binair: 1000&0001 = 0000)
8&2 = 0,
8&3 = 0,
8&4 = 0
8&5 = 0
8&6 = 0
8&7 = 0
8&8 = 1
8&9 = 1
```
```
binair: 1001
9&0 = 0
9&1 = 1, (binair: 1001&0001 = 0001)
9&2 = 0,
9&3 = 1,
9&4 = 0
9&5 = 1
9&6 = 0
9&7 = 1
9&8 = 8
9&9 = 9
```

Permissieniveau's hebben de waardes:
 * 1, 2, 4, 8
 * equivalent in binair:
 * 0001,0010,0100,1000

maar ze zijn in ons geval bijna altijd cumulatief, dus:
 * 1,3,7, (te hoog voor octaal: 15 (2 digit, kan niet met bitwise vergelijken))
 * equivalent in binair:
 * 0001,0011,0111, 1111

als er levels zijn die complementair mogen zijn, dus die voor de ene OF voor de andere groep nuttig zijn, dan kan bijvoorbeeld role met 5 ook nog nuttig zijn.
Namelijk:
 * 5 = 0101, dus ook veel overlap met 7 = 0111, vergelijkbaar als
 * 3 = 0011, maar:
 * als je 3 vraagt ergens, zal je met een role die 5 heeft geen toegang krijgen.
 * een role met 5 staat dus naast een role met 3, niet daarboven!!

## Strings i.p.v. Octalen voor opslaan permissie

De webstek gebruikte eerst octalen. Deze zijn beperkt tot een integere waarde van 7 per positie. Integers kunnen hetzelfde, maar gaan tot een veel hogere ASCII-waarde. Nadeel is dat leesbaarheid slechter is, maar door bij genereren integers te gebruiken, die via een functie worden omgezet in de bijhorende ASCII-tekens is dat geen echt probleem. Als we strings gebruiken gaat PHP kijken naar de ASCII waardes. De ascii-waardes staan voor allerlei gewone én ook niet-gewone symbolen, die dus niet altijd leesbaar zijn... (bijvoorbeeld 8###backspace), maar dat hebben we over voor veel meer waardes!

voor omzetten naar en van ascii waardes:
 * http://php.net/manual/en/function.ord.php  97 = ord(a)
 * http://php.net/manual/en/function.chr.php   a = chr(97)
 * of http://nl3.php.net/manual/en/function.sprintf.php met placeholder %c, zet getallen van hun ASCII waarde om naar het karakter.

tabellen voor waardes met omschrijving:
 * http://www.ascii.cl/
 * http://www.asciitable.com/

Een ander nadeel dat opgelost wordt door strings i.p.v. octalen te gebruiken is het beperkte aantal posities in een permissie. Een octaal is beperkt tot de maximale integer waarde van 32bit bij gebruik van 32bit processoren of 64bit bij gebruik van 64bit processoren. Een string heeft geen lengte beperking.
