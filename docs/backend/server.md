---
layout: default
parent: Backend
nav_order: 1
title: Server
---

# Server

## Verbinden over SSH

Directe verbinding met de server kan relatief eenvoudig over SSH. Hiervoor moet wel je Public Key toegevoegd zijn, zie bijvoorbeeld [deze](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent) tutorial van GitHub hiervoor. 
Voor de veiligheid kan alleen iemand met toegang tot de server deze key toevoegen, vraag daarvoor dus even een huidig PubCie lid.

Als je key is toegevoegd kan je via Windows Powershell of een andere Command Prompt verbinden met het commando:

'ssh csrdelft@csrdelft.nl'

Als dit niet direct werkt, is de server migratie uit 2023 waarschijnlijk nog gaande, probeer dan:

'ssh csrdelft@tzdturbo.knorrie.org'

Als alles goed is gegaan krijg je tzdturbo te zien op je scherm, en nog wat meer login informatie. Hier kan je met de gebruikelijke command line commands doorheen lopen.

## GitHub Veranderingen doorvoeren

Als je nieuwe code hebt toegevoegd en hebt gemerged met de master branch op GitHub, moeten die nog doorgevoerd worden op de server. Gebruik simpelweg:

'composer update-prod'

## Key toevoegen

Als je toegang hebt tot de server zal je Private Key al toegevoegd zijn. Als je een nieuwe key wilt toevoegen, gebruik:

'nano /home/csrdelft/.ssh/authorized_keys'

Hier kun je de nieuwe key toevoegen.

## Bestanden opladen

Als je meerdere bestanden wilt opladen, zoals bijvoorbeeld de novietenfoto's, is het handig om een meer visuele interface te hebben. 
Je kan hiervoor bijvoobeeld FileZilla gebruiken, hiermee kan je gewoon bestanden vanaf je eigen computer naar de server slepen. 
Om te verbinden, ga je naar File -> Site manager. Gebruik dan deze instellingen:

![Filezilla!](/assets/images/Filezilla.png)

Vul dan bij Host hetzelfde in als wat je doet bij het verbinden over SSH, en gebruik de Key File waarmee je ook op de server kan.

## Verbinden met de database
**Hiermee kan aanpassingen maken aan de databases van de Stek, dit heeft direct effect op de live Stek! Doe dit dus alleen als je weet wat je doet.**

Om de database te managen kan je bijvoorbeeld HeidiSQL gebruiken. Voeg een nieuwe sessie toe, en gebruik de volgende instellingen:

![HeidiSQL!](/assets/images/Heidi1.png)

![HeidiSQL!](/assets/images/Heidi1.png)

Het wachtwoord kun je op de server vinden, vraag hiervoor een PubCie lid. Vul bij Host wederom etzelfde in als wat je doet bij het verbinden over SSH, en gebruik de Key File waarmee je ook op de server kan.
