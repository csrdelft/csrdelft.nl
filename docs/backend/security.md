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

## Roles

> [Symfony documentatie over Roles](https://symfony.com/doc/current/security.html#roles)

In `security.yaml` staan alle rollen gedefinieerd. Een gebruiker heeft in ieder geval één ROLE, deze is opgeslagen in het `perm_role` veld in Account.

Er is een rollen hierarchie gedefinieerd zodat er met een specifieke rol meerdere standaardrollen afgevangen kan worden.

Er zijn een aantal standaard rollen in Symfony:

- `ROLE_ALLOWED_TO_SWITCH`: Mag van gebruiker switchen (su)
- `IS_IMPERSONATOR`: Is van gebruiker geswitched
- `PUBLIC_ACCESS`: Het hele internet

## Voters

> [Symfony documentatie over Voters](https://symfony.com/doc/current/security/voters.html)

Het idee van voters is dat ze op basis van een permissie-string kunnen zeggen of een gebruiker toegang heeft. Dit kan algemene toegang zijn, maar ook toegang tot een specifiek object. Dit laatste wordt nog niet gedaan in de stek.

Alle voters zijn te vinden in de `CsrDelft\common\Security\Voters\ ` namespace. Ze implementeren allemaal `Symfony\Component\Security\Core\Authorization\VoterInterface`, maar er is ook een `Voter` class die ge-extend kan worden en al grotendeels geimplementeerd is.

Kijk bijvoorbeeld naar `EerstejaarsVoter` voor een simpele implementatie van een voter die alleen toegang geeft aan eerstejaars.

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

Controleert of het wachtwoord reset formulier goed ingevuld is, als dit het geval is wordt het wachtwoord gereset en wordt de gebuiker ingelogd.

### RemoteLoginAuthenticator

_Geactiveerd wanneer:_ Er een `POST` request wordt gestuurd naar `/remote-login-final`

Controleert of de meegegeven uuid geaccepteerd is door een gebruiker. Als dit het geval is wordt de huidige gebruiker ingelogd in het account van de gebruiker die de uuid heeft geaccepteerd.

Zie ook [Remote Login](./remote-login.md).

### ApiAuthenticator

_Geactiveerd wanneer:_ Path begint met `/API/2.0` en een van de volgende opties

- Path is `/API/2.0/auth/authorize` en method is `POST` en velden `user`, `pass` zijn gezet.
- Path is `/API/2.0/auth/token` en method is `POST` en veld `refresh_token` is gezet.
- Header `HTTP_X_CSR_AUTHORIZATION` is gezet

Deze authenticator doet de volledige jwt flow voor de api.

### OAuth2Authenticator

_Geactiveerd wanneer:_ Path begint met `/api/v3/` en de `Authorization` header begint met `Bearer `

Zie [OAuth](../onderdelen/oauth.md).
