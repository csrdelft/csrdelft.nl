---
layout: default
parent: Onderdelen
nav_order: 1
title: Web Push
---

# Web Push

Dit is het protocol die de stek gebruikt om push berichten te sturen naar de browsers van leden.

## Frontend

De Frontend is nogal gecompliceerd, omdat er veel bij komt kijken. Zie [web.dev](https://web.dev/notifications/) voor een uitgebreide uitleg van de flow en ontwerpen van web push notificaties. Ook [het MDN artikel over Push API](https://developer.mozilla.org/en-US/docs/Web/API/Push_API) is heel handig.

### Flow

1. Check of Push API beschikbaar is
2. Gebruiker selecteert 'ja' op push berichten op `/instellingen#instelling-forum`
3. Gebruiker staat notificaties toe in de browser
4. Check of er een bestaand abonnement is
5. Abonnement wordt aangemaakt voor de browser
6. Informatie over de browser (`client_endpoint` en `client_keys`) worden naar database verstuurd
7. Gebruiker vraagt om berichten op `/instellingen#instelling-forum` of in een draad/deel
8. Bij een bericht wordt de `ServiceWorker` uit `/sw.js` in de browser geactiveerd door de web-push library in de stek (Zie [het MDN artikel over Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API) voor meer over ServiceWorkers)
9. De `ServiceWorker` van de browser rendert een push-notificatie

## Backend

De Backend werkt voor nu met een web-push library in PHP. Zie [`web-push-php`](https://github.com/web-push-libs/web-push-php) voor meer info en documentatie.

De flow van de backend is vrij minimalistisch. De `client_endpoint` en `client_keys` worden met een `uid` die naar een profiel linkt opgeslagen in de `push_abbonement` tabel in de database. Elke keer dat een lid een bericht moet krijgen wordt de informatie opgezocht aan de hand van de `uid` en dan wordt er een push bericht aangemaakt en verstuurd. Dit gebeurt allemaal in de `ForumMeldingenService`. Het format van het bericht is vrij specifiek, want het moet worden opgevangen door de `ServiceWorker` in de frontend. Als er iets mis is, komt het bericht niet aan.

## Configuratie

De Web Push library werkt niet standaard bij een lokale installatie, want er moeten eerst nog een paar VAPID sleutels gegenereerd worden. Zie [de documentatie van de library](https://github.com/web-push-libs/web-push-php#authentication-vapid) voor info over het genereren van de VAPID sleutels. Plaats deze sleutels in `.env.local` bij de passende variabelen:

```shell
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
```
