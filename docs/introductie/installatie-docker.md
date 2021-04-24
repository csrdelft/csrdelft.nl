---
layout: default
parent: Introductie
nav_order: 1
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
    $ docker-compose run composer run-script migrate

    # Voeg een npm dependency toe
    $ docker-compose run yarn add myPackage

    # Als de javascript build om de een of andere reden is omgevallen
    $ docker-compose restart yarn
