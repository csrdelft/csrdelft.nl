---
layout: default
parent: Backend
nav_order: 10
title: Remote Login
---

# Remote Login

_Werk in uitvoering, kan veranderen_

Hier wordt ingelogd in de stek zonder dat er een wachtwoord of gebruikersnaam wordt ingevuld. Met behulp van een qr code
wordt op een ander apparaat ingelogd door de gebruiker. Dit wordt gebruikt om gebruikers (tijdelijke) toegang te geven op gedeelde computers.

## Technische werking

```
class RemoteLogin {
  id: number // In de sessie
  expires: DateTime // Enkele minuten in de toekomst, wordt gereset als de status naar voren gaat
  key: string // Om deze RemoteLogin op te halen
  status: PENDING|ACTIVE|ACCEPTED|REJECTED|EXPIRED
}
```

Status flow
```
PENDING --> ACTIVE --> ACCEPTED
  |           |
  v           v
EXPIRED  REJECTED
(delete)
```

1. Gebruiker navigeert naar `/remote_login`
	1. Er wordt een nieuwe `RemoteLogin` entity gemaakt in de database met een specifieke sleutel. Deze entity is een paar minuten houdbaar. De status is `PENDING`. De sleutel van deze entity wordt in de sessie opgeslagen.
	1. Er wordt een qr code gegenereerd naar `/remote_login_authorize` met de sleutel ingebakken.
	1. Op de achtergrond wordt met regelmaat (iedere paar seconden) de status van de `RemoteLogin` met de sleutel geladen.
1. (op de device) De gebruiker scant de qr code en gaat op zijn (misschien ingelogd) apparaat naar `/remote_login_authorize`
	1. Als de gebruiker op dit device ook niet is ingelogd kan er eerst normaal met gebruikersnaam en wachtwoord worden ingelogd
	1. De status van de `RemoteLogin` entity wordt `ACTIVE`, de pagina op `/remote_login` geeft een laadbalkje weer op de qr code.
1. (op de device) De gebruiker klikt op de 'Autoriseer' link
	1. De status van de `RemoteLogin` entity wordt `ACCEPTED`, de pagina op `/remote_login` kan nu verder.
1. De `/remote_login` pagina post naar `/remote_login` pagina en als de `RemoteLogin` ok is wordt er een sessie gemaakt
1. De verkregen sessie is (voor nu) een `AuthenticationMethod::temporary` sessie
  1. Het doel nu is om een oauth2 sessie te kunnen autoriseren, misschien dat op een later moment het voor andere dingen gebruikt kan worden.
