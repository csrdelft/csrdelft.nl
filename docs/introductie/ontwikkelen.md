---
layout: default
parent: Introductie
nav_order: 1
title: Ontwikkelen
---

# Ontwikkelen aan de stek

Dit document bevat een aantal tips over het ontwikkelen aan de stek. Als er wordt gesproken over een configuratie die uitgevoerd kan worden kun je die in PhpStorm vinden in het uitklapmenu rechtsbovenin, je kan ook bij deze acties komen door twee keer achter elkaar op `ctrl` te klikken en in de balk die dan komt te zoeken. Bij veel kopjes is een uitklap blok "Commando's", hier vindt je specifieke commando's die je kan uitvoeren ipv klikken op knopjes in PhpStorm.

## Git

Als je veranderingen maakt, commit ze dan naar een branch en maak een Pull Request in GitHub. Op deze manier kan er nog iemand naar je code kijken en worden de tests gerund. Als je documentatie veranderingen, hotfixes of kleine veranderingen maakt waarvan je zeker weet dat de boel niet stuk gaat kun je die naar `master` comitten. Als je wat langer bij de PubCie zit kun je deze afweging beter maken.

In principe kun je alle git dingen in GitHub desktop doen. Wil je graag beter begrijpen wat git doet, dan kun je de [Learn Git Branching](https://learngitbranching.js.org/) lessenserie volgen.

## PhpStorm

PhpStorm is een heel erg krachtige IDE met super veel functies. Langzaamaan zul je meer functies ontdekken, maar er zijn een aantal functies die handig zijn vanaf het begin.

|   Sneltoets    | Beschrijving                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| :------------: | :---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `Shift+Shift`  | Twee keer shift indrukken achter elkaar opent de 'Ga naar alles...' zoekbalk. Hiermee kun je zoeken op klassen, functies, bestanden en nog allerlei andere dingen. Gebruik deze als je ongeveer weet welke klasse je nodig hebt, dit is een stuk sneller dan door de bestandsstructuur scrollen om je bestand te vinden.                                                                                                                                                |
|  `Ctrl+Ctrl`   | Twee keer ctrl indrukken achter elkaar opent de 'Voer alles uit...' zoekbalk. Hiermee kun je zoeken in alle commando's die je kan uitvoeren. Dit zijn de voorgedefinieerde commando's in het configuratiemenu, maar ook alle commando's van Symfony.                                                                                                                                                                                                                    |
| `Ctrl+Shift+F` | Opent het zoekscherm, hiermee kun je in het hele project zoeken. Dit is heel handig als je niet precies weet waar je moet zijn. Je kan hier bijvoorbeeld zoeken op een foutmelding die je bij een bug krijgt (als de foutmelding in onze code staat natuurlijk), of je kan zoeken op een classname in de gegenereerde HTML om erachter te komen waar deze html gegenereerd wordt. Je kan zoeken ook beperken tot een specifieke map of een specifieke bestandsextensie. |
|   `Shift+f6`   | Hernoem een variabele, functie of class. PhpStorm zorgt ervoor dat alle referenties naar deze naam ook veranderen. (meestal lukt dit, maar er zijn plekken waar dit niet lukt, controleer dus of het gelukt is en of er niet toevallig nog ergens verwijzingen zijn naar de oude naam)                                                                                                                                                                                  |

## Database

### Dump inladen

Vaak ontwikkel je aan de stek met een database dump van de productieomgeving, dat is makkelijk testen.

In PhpStorm kun je makkelijk een dump laden, zorg ervoor dat de database bestaat en dat alle tabellen bestaan.

- Open de database tab in `View > Database`
- Rechtsklik op `csrdelft@localhost`
- Klik op `Run SQL Script...`
- Kies de dump die je hebt gekregen en klik op `Ok`
- De dump wordt nu geladen, dit kan een paar minuten duren

_Soms falen er een paar statements, meestal is dit niet erg. Als er veel statements falen is er iets mis._

### Migraties uitvoeren

Soms is het nodig om de database te veranderen, hiervoor worden [migraties](../deploy/migraties.md) gemaakt. Hiermee kunnen aanpassingen aan de database met iedereen gecommuniceerd worden en zou het dus nooit nodig moeten zijn om je database te resetten.

Een migratie bevat een aantal SQL statements die veranderingen maken aan de database. Meestal bevat een migratie ook statements om zichzelf ongedaan te maken Soms is dit heel lastig of repareert de migratie verkeerde data. In deze gevallen wordt er geen 'terug' migratie gemaakt. Terug kunnen met een migratie is alleen handig als je lang met een feature bezig bent of als je met meerdere mensen aan een feature gaat werken.

Je kan de `[PHP] Database Migraties` configuratie in PhpStorm uitvoeren om migraties uit te voeren. Het kan geen kwaad om dit vaak te doen, als er geen migraties zijn maakt het namelijk niet uit.

<details>
<summary><strong>Commando's</strong></summary>

<pre>
php bin/console doctrine:migrations:migrate
</pre>

</details>

### Database resetten

Soms gaat er iets mis met de database, iemand heeft een fout gemaakt in een migratie, of je hebt per ongeluk iets verwijderd. Als dit zo is kun je het makkelijkst een dump laden.

Om een database te resetten moet je eerst de database weggooien (`[PHP] Database Verwijdern`), dan de database aanmaken (`[PHP] Database Aanmaken`), migraties uitvoeren (`[PHP] Database Migraties`, type `yes` in de Run dialoog) en hierna kun je een dump terugzetten (zie het kopje Dump inladen).

<details>
<summary><strong>Commando's</strong></summary>

De volgende commando's kun je in de commandline uitvoeren:

<pre>
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
# Op windows met wampserver staat mysql.exe in
# C:\wamp64\bin\mariadb\mariadb10.3.23\bin\
mysql -u root -p csrdelft -e "source dump.sql"
</pre>

</details>
