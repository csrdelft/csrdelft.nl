# Panorama-spellen

Dashboard: `/panorama/spellen`. Quizbeheer: `/panorama/spellen/beheer`. Spelbeheer: `/panorama/spellen/spelbeheer`. De oude expeditie-ingang verwijst door naar het dashboard.

## Spellen

- **Bergroute**: trek een route van start naar top via één vlag op makkelijk of twee vlaggen op normaal/moeilijk. Kaarten zijn 4×4 of 5×5, met enkele afkortingen. Paden kosten één tot drie wandelminuten; twee optionele uitkijkpunten geven bonus. Er zijn geen waterreserves, bronnen of sleutels. Bereikbare volgende punten lichten op en bezochte vlaggen kleuren groen. De server controleert de route en vergelijkt de kwaliteit met het optimum. Bediening: slepen, klikken of pijltjes; Backspace of de knop doet een stap terug.
- **Gebroken uitzicht**: wissel twee stukken van een panorama. Wissels verschijnen direct en worden gebundeld opgeslagen. De server controleert de verdeling en het minimumaantal wissels.
- **Dubbel zien**: memory met verborgen kaarten op de server. Snelle opeenvolgende klikken worden verwerkt zonder op elke reactie te hoeven wachten.
- **De verrekijker**: herken foto-uitsneden uit vier echte foto's. Zowel uitsnede als antwoorden zijn grijs, zodat kleurvergelijking onvoldoende is. Uitsneden wisselen over negen fotogebieden, inclusief randen en hoeken, met lichte variatie binnen ieder gebied. Dezelfde ronde behoudt zijn uitsnede na vernieuwen. Bronnen en CC0-licenties staan in `docs/panorama-fotobronnen.json`.
- **Sterrenpad**: onthoud steeds langere patronen. Afspelen duurt 340 ms per ster. Invoer reageert direct; een volledig patroon wordt in één verzoek gecontroleerd. Toetsen 1–9 werken ook.

Makkelijk en normaal geven wisselende opgaven. Moeilijk gebruikt een vaste wedstrijdbaan per spel. De serverklok begint na het genereren van de opgave. Tijd loopt door na het sluiten van een tabblad. Een nog niet ingestuurde bergroute blijft alleen in het huidige tabblad; nog niet bevestigde puzzelwissels kunnen bij onmiddellijk sluiten verloren gaan.

## Punten en onderscheidingen

De beste score per spel telt: 900, 1.400 of 2.000 basispunten naar niveau. Tijd en fouten kosten punten. De beeldpuzzel trekt extra wissels af; de bergroute trekt 35 punten per eenheid boven de optimale nettokosten af. Bij de verrekijker telt ook het aandeel goede antwoorden. Herhaald spelen stapelt geen arcadescores op.

Dagquizpunten komen bovenop de vijf beste scores. Er zijn medailles, prestaties en wisseltrofeeën voor de snelste foutloze poging op moeilijk. Voor de route en beeldpuzzel is bovendien een optimale oplossing nodig. Dit zijn virtuele onderscheidingen, geen toegezegde materiële prijzen.

## Dagquiz en commissie

De dagvraag staat boven de spelkeuze. Een compacte tussenstand toont deelnemers, percentage goed en het aantal dat het in één poging wist. Iedere deelnemer telt één keer; ook nog niet afgeronde pogingen tellen mee in het percentage. Zonder deelnemers wordt geen verzonnen percentage getoond. Na afloop toont de pagina het juiste antwoord en de volgende speeldag.

Iedereen krijgt dezelfde gepubliceerde vraag voor de huidige datum in Europe/Amsterdam. Per account zijn er drie verschillende pogingen: een goed antwoord levert respectievelijk 200, 120 of 60 punten op, eenmaal per vraag. Hoofdletters, accenten, spaties en leestekens worden genormaliseerd. Langere namen verdragen beperkte typefouten en verwisselde letters; korte namen moeten exact zijn. Meerdere geaccepteerde schrijfwijzen kunnen apart worden ingevoerd.

Alleen `commissie:LustrumCie` krijgt toegang tot beheer, gegevens en uploadroutes. Dit gebruikt de bestaande commissierechten; er staat geen account-uitzondering in de code. Vragen hebben een datum, vraagtekst, antwoordvarianten, optionele foto, bronvermelding en publicatiestatus. Vragen met pogingen en verstreken dagen zijn niet meer wijzigbaar. Toekomstige of ongepubliceerde vragen en foto's blijven ontoegankelijk voor gewone leden.

Uploads worden op werkelijk afbeeldingstype, afmetingen en bestandsgrootte gecontroleerd, verkleind en opnieuw opgeslagen als WebP zonder metadata. Bestanden staan privé in `data/panorama-quiz`, met willekeurige namen. De afbeeldingroute controleert het account en de publicatiedatum. CSRF-beveiliging geldt ook voor uploads.

In de lokale ontwikkelkopie is LustrumCie Oosterhuis 2025-2027 met het opgegeven eigen lidmaatschap toegevoegd. De live ledenadministratie is niet aangepast. De lokale voorbeeldvraag is geen migratieseed: na publicatie moeten commissieleden zelf vragen toevoegen.

## Spelbeheer

Alleen de bestaande bevoegdheid `commissie:LustrumCie` opent de pagina, instellingen en uploadroutes. Per spel kan de commissie de naam wijzigen, nieuwe pogingen sluiten/openen en de beeldselectie aanpassen. Bij Bergroute en Sterrenpad gaat het om achtergronden; bij puzzel, memory en verrekijker om de spelbeelden zelf. Memory vereist minstens tien verschillende beelden, de verrekijker minstens vier echte foto's. De bestaande landschappen en foto's blijven als keuzes beschikbaar.

Foto's kunnen met fotograaf/bron worden geüpload. Dezelfde servercontrole en hercodering als de dagquiz worden toegepast. Foto's staan privé; ingelogde leden krijgen ze via een gecontroleerde afbeeldingroute. Uploads kunnen bij meerdere spellen worden geselecteerd. Alleen toevoegen verandert nog geen spel; daarna moet de selectie worden opgeslagen. Instellingen hebben een revisie zodat commissieleden elkaars wijzigingen niet ongemerkt overschrijven.

Een andere beeldselectie bij puzzel, memory of verrekijker verandert de uitdaging: records van uitsluitend dat spel worden transactioneel gearchiveerd en een lopende poging voor dat spel vervalt. De medaillehistorie blijft behouden. Achtergronden en namen wijzigen of spellen sluiten archiveren geen scores; gesloten spellen staan niet meer tussen de speelknoppen en kunnen ook niet via een rechtstreeks verzoek worden gestart. Een bestaande poging mag nog worden voltooid.

## Opslag en migraties

`Version20261027190000` bevat de eerdere spelertabellen. `Version20261028120000` voegt quizvragen en antwoorden toe. Bestaande records van de gewijzigde route-, verrekijker- en sterrenspellen worden privé gearchiveerd, zodat oude en nieuwe opgaven niet in hetzelfde klassement worden vergeleken. Memory- en puzzelrecords blijven behouden. Oude expeditiegegevens en teamtabellen worden niet gewist. `Version20261028140000` voegt de centrale spelinstellingen toe en archiveert records van de vorige, ingewikkelde Bergroute. Andere actieve spellen blijven behouden.

Scores, identiteit, tijd en zetten worden op de server gecontroleerd. Revisies, poging-ID's, transacties en unieke quizantwoorden voorkomen herhaalde beloningen. De browser ontvangt geen gesloten memorydeck, route-oplossing of toekomstige antwoorden. Mutatiereacties laten dure klassementsberekeningen weg; het dashboard ververst deze afzonderlijk. Reacties zijn `private, no-store`.

De spelkeuze gebruikt de echte Panorama-thematekening, kleuren uit de poster, vijf eenvoudige spelillustraties en een apart dagvraagvlak. De medaillevormgeving blijft behouden. De indeling is gecontroleerd op desktop en op 390, 768 en 1024 pixels.

De twaalf SVG-landschappen blijven beschikbaar binnen spellen. De losse galerij en landschapsnamen zijn verwijderd. Er worden geen externe beeld- of lettertypediensten tijdens het spelen aangeroepen.

## Verificatie

35 PHP-tests met 5.131 assertions zijn geslaagd: alle spellen op drie niveaus, route-optimalisatie, verborgen informatie, scores, hervatten, verouderde acties, quiztypfouten, datums, uploads en eenmalige beloningen. De nieuwe tests controleren ook eenvoudigere kaarten, afgesloten spellen, beeldselectie, verouderde beheerwijzigingen, geüploade spelbeelden en behoud van gegevens bij de routemigratie. Databasefixtures worden transactioneel teruggedraaid; de tests gebruiken alleen een expliciet ingestelde lokale testdatabase.

Een echte HTTP-controle bevestigt toegang voor een commissielid, weigering voor gewone leden en anonieme bezoekers, CSRF-controle en afscherming van toekomstige foto's. De browsertest gebruikt echte Twig, gebouwde bestanden en de echte spelservices, met 140 ms nagebootste netwerkvertraging. Alle vijf spellen, snelle invoer, dagquiz, beheer, upload en planning zijn getest op desktop en mobiel. Spelbeheer is gecontroleerd door een spel te sluiten/openen, de naam te wijzigen en een geüploade foto te selecteren en daadwerkelijk in de puzzel te gebruiken. Testgegevens zijn opgeruimd.

ESLint, TypeScript, Psalm, Twig-controle en de ontwikkelbuild zijn geslaagd. De nieuwe Doctrine-mapping komt overeen met het lokale schema. Publiceer via de normale GitHub-controles en voer daarna op de server `composer update-prod` uit. Controleer de actuele serverversie en de migraties voordat je publiceert.

## Voorbereide fotovragen

Vijf nieuwe CC0-fotovragen staan in de lokale database ingepland op de eerstvolgende vrije dagen, zonder bestaande vragen te overschrijven. Onderwerpen zijn een stad, brug, Grieks eiland, berg en land. Antwoordvarianten, fotograaf en originele bestandsbronnen staan in `docs/panorama-dagvragen-bronnen.json`. Foto’s zijn visueel gecontroleerd en via de normale uploadverwerking privé opgeslagen. De commissie kan de geplande vragen in het beheer aanpassen; toekomstige vragen blijven verborgen voor andere leden. Dit is lokale inhoud, geen automatisch uitgevoerde productieseed.

De aantallen deelnemers en in-één-keer-goed staan standaard uit. Commissieleden kunnen deze onafhankelijk aanzetten bij Dagvragen beheren → Zichtbare cijfers. Het percentage goed blijft zichtbaar. De instelling gebruikt dezelfde beveiligde, revisiegecontroleerde opslag als spelbeheer.

## Publicatie zonder aankondiging

Er komen geen verwijzingen vanuit de bestaande homepage of menu’s. Alleen de nieuwe Panorama-pagina’s linken onderling. De normale ledenlogin blijft verplicht. HTML en responses krijgen `noindex, nofollow, noarchive`; de bestaande robots- en sitemapbestanden blijven ongewijzigd.

De lokale basissnapshot is voor deze publicatie vergeleken met de opgehaalde `origin/master`; beide stonden op `58d27c2bb`. De productie-URL’s gaven vóór publicatie een 404. De daadwerkelijke checkout op de server moet via SSH gecontroleerd worden. Lokale spelstanden, ledenaanpassingen, inloggegevens en quizuploads worden nooit als code meegenomen. De voorbereide quizvragen en hun afbeeldingen moeten afzonderlijk via het beheer of een gecontroleerde inhoudsimport worden overgezet.
