---
layout: default
parent: Onderdelen
nav_order: 1
title: OAuth
---

# OAuth

Werk in uitvoering hiero

Maak het mogelijk voor externe partijen om in te loggen met een C.S.R. webstek account en ook om met eventuele api's te verbinden.

Zie [`oauth2-bundle`](https://github.com/trikoder/oauth2-bundle) voor meer info en documentatie.

We zitten op `3.x-dev` van oauth2-bundle omdat Guard Authenticators nog niet in een gereleasde versie zitten.

De `trikoder:oauth2:` commands zijn er om clients te beheren.

## Configuratie

De volgende velden moeten worden gezet in `.env.local`:

```
OAUTH2_PRIVATE_KEY_PATH=
OAUTH2_PUBLIC_KEY_PATH=
OAUTH2_ENCRYPTION_KEY=
```

Zie https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys voor info over het genereren van sleutels. Plaats deze sleutels in `OAUTH2_PRIVATE_KEY_PATH` en `OAUTH2_PUBLIC_KEY_PATH`.

Stop een random string in `OAUTH2_ENCRYPTION_KEY`

## Een Client maken

Zie hiervoor ook de docs van oauth2-bundle: https://github.com/trikoder/oauth2-bundle/blob/v3.x/docs/basic-setup.md

Let op dat bij het `trikoder:oauth2:update-client` command je alle velden moet meegeven, anders worden ze leeg gemaakt.
