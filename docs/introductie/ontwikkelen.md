---
layout: default
parent: Introductie
nav_order: 1
title: Ontwikkelen
---

# Ontwikkelen aan de stek

Dit document bevat een aantal tips over het ontwikkelen aan de stek. Als er wordt gesproken over een configuratie die uitgevoerd kan worden kun je die in PhpStorm vinden in het uitklapmenu rechtsbovenin, je kan ook bij deze acties komen door twee keer achter elkaar op `ctrl` te klikken en in de balk die dan komt te zoeken. Bij veel kopjes is een uitklap blok "Commando's", hier vindt je specifieke commando's die je kan uitvoeren ipv klikken op knopjes in PhpStorm.

## Database

### Dump inladen

Vaak ontwikkel je aan de stek met een database dump van de productieomgeving, dat is makkelijk testen.

In PhpStorm kun je makkelijk een dump laden, zorg ervoor dat de database bestaat en dat alle tabellen bestaan.

* Open de database tab in `View > Database`
* Rechtsklik op `csrdelft@localhost`
* Klik op `Run SQL Script...`
* Kies de dump die je hebt gekregen en klik op `Ok`
* De dump wordt nu geladen, dit kan een paar minuten duren

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
