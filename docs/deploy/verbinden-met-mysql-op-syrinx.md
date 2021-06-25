---
layout: default
parent: Deploy
nav_order: 1
title: MySql op Syrinx
---

# Verbinden met MySql op Syrinx

MySql op Syrinx is niet ge-exposed op het internet, je moet dus via ssh inloggen op de server. HeidiSQL kan dit gelukkig heel makkelijk doen.

Eerst moet je `plink.exe` en `puttygen.exe` downloaden van de [PuTTY website](https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html). Met puttygen kan je de private key die je gebruikt voor git omzetten naar een variant de gesnapt wordt door HeidiSQL, zie [simplified.guide/putty/convert-ssh-key-to-ppk](https://www.simplified.guide/putty/convert-ssh-key-to-ppk)

Maak een nieuwe sessie aan in HeidiSQL met netwerktype 'MySQL (SSH tunnel)', onder het tabje 'SSH-tunnel' kun je nu alle velden invullen, gebruik hier de PuTTY private key die je net gemaakt hebt.

In het instellingen tabje kun je de gebruikersnaam en wachtwoord van de MySQL server invullen. Het wachtwoord en de gebruikersnaam kun je van Syrinx pluken.

Als alles goed werkt kun je met de SQL server verbinden.

## Tabellen naar je lokale stek trekken

Als er specifieke data is die je op je lokale stek up to date wil hebben kun je onder Tools > Onderhoud bij sql export specifieke tabellen exporteren naar je lokale database.
