---
layout: default
parent: Backend
nav_order: 1
title: Email
---

# Email

## Lokaal mails versturen

Het makkelijkst is om sendmail in te stellen met je eigen gmail account als SMTP server. Deze [guide](https://websistent.com/using-sendmail-on-windows/) kan je helpen om sendmail op te zetten op Windows. Als je 2F aan hebt staan moet je via accounts.google.com een app wachtwoord maken voor je gmail en deze gebruiken.

Standaard wordt mail als melding getoond. Door de check in `Mail::send` weg te halen kun je mails sturen. Standaard zijn alle emailadressen het adres van de pubcie, zodat je niet per ongeluk mailtjes naar willekeurige mensen stuurt. Check `Mail::production_safe` als je dit wil veranderen naar je eigen emailadres.

## Courant

Om de courant te testen moet je een lokale sendmail server draaien op poort 25. Maar de courant kun je ook testen door het voorbeeld te bekijken.
