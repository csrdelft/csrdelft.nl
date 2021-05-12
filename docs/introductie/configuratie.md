---
layout: default
parent: Introductie
nav_order: 2
title: Configuratie
---

# Configuratie

Er zijn een aantal dingen te configureren aan de stek. Als je de instructies hebt gevolgd hoef je niets te configureren, maar als je specifieke subsystemen wil testen of als je omgeving net iets anders is kun je de configuratie aanpassen.

In het bestand `.env` staan alle mogelijke configuratie velden. De meeste zijn standaard leeg.

Als je een veld in `.env` wil overschrijven, pas dan niet dit bestand aan, want dan blijft hij vragen of je de verandering wil committen. In plaats daar van kun je een `.env.local` bestand maken. Alle velden die je in dit bestand zet zullen de waardes in `.env` overschrijven.

Kijk naar de documentatie van [symfony/dotenv](https://symfony.com/doc/current/components/dotenv.html) voor meer informatie over hoe de configuratie werkt.

## Configuratie voor prod / dev

De configuratie voor productie en development is net iets anders, hier voor zijn de bestanden `.env.prod` en `.env.dev`, deze bevatten velden die specifiek zijn voor een test stek of een productie stek. Als je deze velden wil overschrijven kun je respectievelijk voor development en productie een `.env.dev.local` en een `.env.prod.local` bestand maken.

## Configuratie velden

De verschillende velden in `.env` zijn verdeeld in verschillende blokken. Hier onder worden alle blokken kort besproken.

### symfony/framework-bundle

Deze velden zijn specifiek voor Symfony. Hier kun je instellen of de stek in dev of prod modus staat.

### doctrine/doctrine-bundle

Deze velden zijn van Doctrine, specifiek kun je hier de database url aanpassen als je database een anderen naam of wachtwoord dan de standaard installatie heeft.

### google/apiclient

Met deze velden kun je er voor zorgen dat GoogleSync werkt.

### csrmail

Hier gaat het wachtwoord van de maillijsten, deze heb je lokaal nooit nodig.

### emails

Om geen emails op te slaan in de broncode van de stek staan de emailadressen in de configuratie. Voor de werking van de stek heb je deze niet nodig.

### google

Laat Maps embed en Recaptcha werken

### jwt

JWT wordt gebruikt door de api om sleutels te genereren.

### ldap

De gegevens van de LDAP server van Knorrie

### pin_transactie_download

De configuratie van de pin transactie download tool. Kijk op Syrinx voor de configuratie als je hier aan gaat sleutelen.

### slack

De configuratie van de error logger naar slack. Als je deze op je teststek aan zet worden je commissiegenoten lastig gevallen.

### sponsor_affiliates_download

Als je gaat sleutelen aan de sponsor extensie kijk dan op Syrinx voor de configuratie.

### config

Overige velden van de stek zoals waar imagemagick te vinden is. Je kan hier ook aanpassen wat de url van je test stek is. (Om deze te overriden heb je wel een `.env.dev.local` bestand nodig!)

### directories

Waar is de broncode van de stek te vinden (hoef je meestal niet aan te passen)

### memcached

Pas deze velden aan als je Memcached hebt geinstalleerd. Zie ook [Caching](../backend/caching.md).
