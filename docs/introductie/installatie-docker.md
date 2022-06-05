---
layout: default
parent: Introductie
nav_order: 3
title: Installatie Docker
---

# Installatie met Docker

Het is mogelijk om de stek met Docker te draaien. Dit is eigenlijk alleen nodig als je Docker al geinstalleerd hebt en al een beetje met Docker overweg kan.

Installeer [Docker](http://docker.com).

Kopieer de database dump naar de `data` map. Kopieer de plaetjes naar de `htdocs/plaetjes` map. Kopieer eventuele pasfoto's en fotoalbums naar `data/foto`

    # Start alles op, dit duurt de eerste keer ongeveer een kwartier
    $ docker-compose up

Dit zet alles klaar om de stek te runnen. Als dit klaar is kun je naar `http://localhost:8080` navigeren. Aanpassingen worden direct doorgevoerd.

Je kan met de database verbinden op `localhost:3307`, met PhpStorm, HeidiSQL, of wat anders. Met gebruikersnaam `csrdelft`, wachtwoord `bl44t` op database `csrdelft`.

Handige Docker commando's

    # Voeg een php dependency toe
    $ docker-compose run composer require myVendor/package

    # Voer de migraties uit
    $ docker-compose run stek php /app/bin/console doctrine:migrations:migrate

    # Laad de database fixtures
    $ docker-compose run stek php /app/bin/console doctrine:fixtures:load

    # Voeg een npm dependency toe
    $ docker-compose run yarn add myPackage

    # Als de javascript build om de een of andere reden is omgevallen
    $ docker-compose restart yarn

## GitHub Codespaces

Zolang GitHub Codespaces in beta is is het gratis. Vanuit codespaces kun je de stek starten met docker. `docker-compose` is standaard geinstalleerd in Codespaces.

Gebruik de fixtures als je in Codespaces gaat ontwikkelen. Het instellen van een productiedatabase is niet wat je wil in deze omgeving.

Om de stek te bezoeken ga je naar de Docker tab (een van de icoontjes links) en klik je in het context menu van de `csrdelft/stek` container op "Open in Browser".
