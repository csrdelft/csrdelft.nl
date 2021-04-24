---
layout: default
parent: Backend
nav_order: 1
title: Twig
---

# Twig

Twig is de template taal van Symfony en wordt gebruikt om html te genereren op basis van modellen.

Op [twig.symfony.com](https://twig.symfony.com/) staat een goede uitleg over wat twig allemaal kan en als je aan de slag gaat met twig is het aanbevolen om dit door te lezen en als als naslagwerk te gebruiken.

Twig templates zijn te vinden in de `templates` map in de repository.

## Functies in Twig

Twig kan niet zomaar php functies aanroepen, hier heb je Twig extensies voor nodig. Kijk in de `CsrDelft\Twig\Extension\ ` namespace voor de extensies die we nu hebben. Al deze extensies worden automatisch aangevuld door PhpStorm bij het bewerken van Twig bestanden.

## Bestandsnaam van twig bestanden

Twig bestanden hebben meestal de `.html.twig` extensie, dit geeft aan dat het een html bestand is met twig template tags er in. Maar er is bijvoorbeeld ook `profiel/vcard.ical.twig` hier wordt de ical escaper uitgevoerd. Kijk in `CsrDelft\Twig\AutoEscapeService` om te zien welke bestandsextensies wij definieren en in `CsrDelft\Twig\Configurator` voor de implementatie van de escapers.
