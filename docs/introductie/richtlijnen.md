---
layout: default
parent: Introductie
nav_order: 9
title: Richtlijnen
---

# Richtlijnen

Dit document bevat richtlijnen voor het ontwikkelen aan de stek. Pas ze vooral aan of start een discussie als je het niet eens bent met deze richtlijnen.

## Algemeen

- Er is ruimte om het wiel uit te vinden, dit kan een goede oefening zijn. Denk wel na of je het wiel opnieuw wil uitvinden, vaak kun je ook veel leren van het implementeren van een bestaande library/applicatie.
- Maak voor zo veel mogelijk veranderingen een pull request, zodat er makkelijker overzicht te houden is wat er veranderd.
- Vraag bij een pull request om reviews.
- Maak pull requests zo klein mogelijk, zodat reviews sneller gaan en veranderingen makkelijker te begrijpen zijn.
- Maak issues aan als je dingen tegen komt die kapot zijn of die niet werken zoals je verwacht, probeer de issue zo volledig mogelijk te maken. Vooral met wat je had verwacht als er iets onverwachts gebeurde. Plak er ook wat labels aan.

## Frontend

- Gebruik zo min mogelijk jQuery, meestal is dit niet nodig.
- Overweeg Vue als je een complexer onderdeel maakt
- Externe Stek: Laad zo min mogelijk externe libraries op de externe stek
- Probeer zo veel mogelijk algemene/generieke/semantic css te schrijven, zodat de css hergebruikt kan worden.

## Backend

- Volg zo veel mogelijk de manier waarop Symfony dingen aanpakt bij het maken van onderdelen. Dit zorgt voor consistentie en Symfony kiest meestal een goede aanpak.
- De APIs in lib/controller/api zijn voor gebruik door externe tools. Verbonden met oauth (v3) of jwt (v2).
- Genereer geen html in PHP code (gebeurt helaas nog op veel plekken). Gebruik hier Twig templates voor.
- Plaats geen (of zo min mogelijk) JavaSript in PHP/Twig, gebruik hier de context voor zoals beschreven in [TypeScript](../frontent/typescript.md).
