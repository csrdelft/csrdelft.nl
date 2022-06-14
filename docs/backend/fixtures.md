---
layout: default
parent: Backend
nav_order: 1
title: Fixtures
---

# Fixtures

_Fixtures zijn een werk in uitvoering. Er zijn nu net genoeg fixtures om in te kunnen loggen. Er moeten er nog een boel bijgebouwd worden._

Er zijn fixtures om data in de database te laden zonder dat je een productiedatabase nodig hebt. Dit kan handing zijn voor het maken van tests.

De fixture generators gaan altijd uit van een database waar alle migraties op uitgevoerd zijn.

Fixtures worden in de tests gebruikt als een basis, zodat het niet meer nodig is om alle data met de hand op te bouwen.

## Fixtures laden

Voordat je fixtures gaat laden is het belangrijk om te controleren of je huidige database weggegooid mag worden.

Run `[PHP] Dotenv status` in PhpStorm en controleer de waarde van `DATABASE_URL` of dit naar de goede database verwijst. Verander anders in `.env.local` de database naar bijv. `csrdelft_test`.

Met `[PHP] Database Fixtures` in PhpStorm kunnen de fixtures geladen worden, **dit leegt de database!**

De fixtures bevatten ook het account `x101` met wachtwoord `stek open u voor mij!`, dit account heeft PubCie rechten.

## Fixtures maken

Met de maker bundle kun je makkelijk nieuwe fixtures genereren. Run `php bin/console make:fixtures` om nieuwe fixtures te maken. In de map `lib/DataFixtures` staan alle fixtures. Je kan hier afkijken hoe het gedaan wordt.

Voor fixtures kun je ook `fzaninotto/faker` gebruiken om fake data te genereren.

## Fixtures in tests

In tests kun je gebruik maken van de data gegenereerd in de fixtures. Bijvoorbeeld de AccountFixtures, als je naar een fixture wil verwijzen maak dan een const met de id of uid van de data waar je het over hebt.

Zie als voorbeeld `GeslachtVoterTest`, in de fixtures wordt de boel goed gezet en in de test wordt naar de specifieke accounts die hier goed gezet zijn verwezen.

Je kan er bij een test altijd vanuit gaan dat er een verse database staat, maar probeer alsnog de database netjes achter te laten na een test.

Let op met Faker als je fixtures voor testen wil gebruiken, gegevens die iedere keer veranderen zijn hier natuurlijk onhandig.
