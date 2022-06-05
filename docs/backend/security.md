---
layout: default
parent: Backend
nav_order: 1
title: Security
---

# Security

Voor inloggen wordt [Symfony Security](https://symfony.com/doc/current/security.html) gebruikt met [Experimental Authenticators](https://symfony.com/doc/current/security/experimental_authenticators.html).

Een lid die een `Account` heeft mag inloggen in de stek. Zodra een lid is ingelogd wordt ons eigen permissiesysteem gebruikt, zie [Permissies](permissies.md).

In `config/packages/security.yaml` is alles wat met security te maken heeft geconfigureerd.

## Authenticators

Er zijn een aantal Authenticators, een authenticator is verantwoordelijk voor het toegang geven tot een specifiek onderdeel van de stek. Een authenticator vangt requests af op basis van een `supports` methode. In deze methode wordt gekeken of de specifieke authenticator overweg kan met de specifieke request, bijvoorbeeld op basis van path, cookie, header, etc.

Een authenticator gooit een `AccessException` of returned een `Passport`, de passport wordt daarna afgehandeld.

Authenticators zijn nog een beetje experimentele technologie, het kan dus zijn dat bij het updaten (van patch versies) van Symfony er iets stuk gaat.

De volgende authenticators worden gebruikt.

### FormLoginAuthenticator

_Geactiveerd wanneer:_ De path is `app_login_check` (`/login_check`) en method is `POST`.

Zit in Symfony gebakken, maakt een Passport met PasswordCredentials, die door het systeem gecontroleerd wordt.

### RememberMeAuthenticator

_Geactiveerd wanneer:_ Er een cookie bestaat die `REMEMBERME` heet.

Zit in Symfony gebakken, ververst de cookie en logt de gebruiker in.

### PrivateTokenAuthenticator

_Geactiveerd wanneer:_ De request een veld heeft die `private_auth_token` heet en dit veld 150 tekens lang is.

Controleert de private token van een gebruiker om een specifieke route te bezoeken. Zie bijv. `AgendaController::ical` voor een route waar dit wordt gebruikt.

### WachtwoordResetAuthenticator

_Geactiveerd wanneer:_ De sessie een `wachtwoord_reset_token` bevat.

Controleerd of het wachtwoord reset formulier goed ingevuld is, als dit het geval is wordt het wachtwoord gereset en wordt de gebuiker ingelogd.

### ApiAuthenticator

_Geactiveerd wanneer:_ Path begint met `/API/2.0` en een van de volgende opties

- Path is `/API/2.0/auth/authorize` en method is `POST` en velden `user`, `pass` zijn gezet.
- Path is `/API/2.0/auth/token` en method is `POST` en veld `refresh_token` is gezet.
- Header `HTTP_X_CSR_AUTHORIZATION` is gezet

Deze authenticator doet de volledige jwt flow voor de api.

### OAuth2Authenticator

_Geactiveerd wanneer:_ Path begint met `/api/v3/` en de `Authorization` header begint met `Bearer `

Zie [OAuth](../onderdelen/oauth.md).
