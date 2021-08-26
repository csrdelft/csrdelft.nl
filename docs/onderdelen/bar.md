---
layout: default
parent: Onderdelen
nav_order: 1
title: Barsysteem
---

# Barsysteem

Het barsysteem heeft twee onderdelen, de api en de frontend.

## API

De api is te vinden in `BarSysteemController` en is onderdeel van de v3 [api](../backend/api.md).

De autorisatie van het barsysteem is een beetje bijzonder, het is niet de bedoeling dat het makkelijk is voor iedereen om in te loggen in dit systeem. Daarom kunnen alleen leden met voldoende rechten (`P_FISCAAT_MOD`) inloggen op het barsysteem. Een mod kan na het inloggen ervoor kiezen om een bepaald apparaat te vertrouwen, een vertrouwd apparaat kan gebruikt worden door de SocCie en door leden om op in te loggen. Hierdoor wordt de toegang tot het systeem beperkt tot moderators en een beperkt aantal apparaten en blijft het mogelijk om in de gaten te houden wat er gaande is.

## Frontend

De frontend van het barsysteem is te vinden in de [`csrdelft/bar`](https://github.com/csrdelft/bar) repository.

