---
layout: default
parent: Onderdelen
nav_order: 1
title: OAuth
---

# OAuth

Maak het mogelijk voor externe partijen om in te loggen met een C.S.R. webstek account en ook om met eventuele api's te verbinden.

Zie [`oauth2-bundle`](https://github.com/trikoder/oauth2-bundle) voor meer info en documentatie.

We zitten op `3.x-dev` van oauth2-bundle, omdat Guard Authenticators nog niet in een gereleasde versie zitten.

De `trikoder:oauth2:` commands zijn er om clients te beheren.

## Flow

De OAuth2 flow is als volgt:
1. De gebruiker komt aan bij een externe service en klikt op de knop om in te loggen met zijn C.S.R. Delft stek account.
1. De gebruiker wordt doorverwezen naar https://csrdelft.nl/authorize?client_id=...&...
	1. `OAUth2AuthorizeListener` pikt dit op en toont een pagina met uitleg aan de gebruiker
	1. De gebruiker klikt op Autoriseer
1. De gebruiker wordt doorverwezen naar de `redirect_uri` die de externe service heeft opgegeven, met een token.
1. De externe service ontvangt een refresh_token
1. De externe service kan de refresh_token gebruiken om bij `/api/v3/token` een access_token op te vragen.
1. De externe service
	1. Weet dat de gebruiker een C.S.R. stek account heeft
	1. Kan requests maken naar pagina's in `/api/v3/`

Vaak wordt hierna nog een request gemaakt naar `/api/v3/profiel` om meer info op te vragen over de gebruiker.

## Scopes

In OAuth2 worden scopes gebruikt om aan te geven tot welke onderdelen een client toegang heeft. Kijk in de `OAuth2Scope` enum voor alle scopes.

In de code can op scope gecontroleerd worden door de rol van de gebruiker te controleren, deze roles hebben altijd de vorm `ROLE_OAUTH2_<scopenaam>`. Bijvoorbeeld om de scope `PROFIEL:EMAIL` te checken:

```php
function heeftScopeProfielEmail(\Symfony\Component\Security\Core\Security $security) {
	return $security->isGranted('ROLE_OAUTH2_PROFIEL:EMAIL');
}
```

## Configuratie

De OAuth2 server werkt niet standaard bij een lokale installatie, er moeten eerst nog een paar sleutels gegenereerd worden.

De volgende velden moeten worden gezet in `.env.local`:

```
OAUTH2_PRIVATE_KEY_PATH=
OAUTH2_PUBLIC_KEY_PATH=
OAUTH2_ENCRYPTION_KEY=
```

Zie [de documentatie van de onderliggende OAuth 2 server](https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys) voor info over het genereren van sleutels. Plaats deze sleutels in `OAUTH2_PRIVATE_KEY_PATH` en `OAUTH2_PUBLIC_KEY_PATH`.

Stop een random string in `OAUTH2_ENCRYPTION_KEY`

## Een Client maken

Zie hiervoor ook de [docs van oauth2-bundle](https://github.com/trikoder/oauth2-bundle/blob/v3.x/docs/basic-setup.md)

Let op dat bij het `trikoder:oauth2:update-client` command je alle velden moet meegeven, anders worden ze leeg gemaakt.
