---
layout: default
parent: Onderdelen
nav_order: 1
title: Pinmatchtool
---

# Pinmatchtool

Pintransacties bij de bar worden handmatig ingevoerd in het SocCie-systeem.
Helaas komt het regelmatig voor dat een bestelling wel ingevoerd wordt,
maar de daadwerkelijke pintransactie niet slaagt, bijvoorbeeld door problemen
met het pinapparaat, te weinig saldo of een verkeerde pincode. Ook wordt er
zo nu en dan vergeten om een pinbestelling in te voeren in het SocCie-systeem
en wordt er wel eens een typfout gemaakt.

Om achteraf deze fouten te ontdekken en te herstellen is de pinmatchtool ontwikkeld.
De pinmatchtool haalt pinbestellingen uit het SocCie-systeem op en probeert deze
te matchen aan pintransacties van het pinapparaat.

## Ophalen pintransacties

Tot november 2022 werden pintransacties opgehaald van PayPlaza. Dit gebeurde door
hun portaal te scrapen. Omdat dit bij veranderingen aan het portaal problemen opleverde
en dus onbetrouwbaar was, is dit vervangen door een koppeling met de
[Rabo Smart Pay Merchant Services API](https://developer.rabobank.nl/product/9773/api/9760#/RaboSmartPayMerchantServicesAPI_3214/overview).

Voor deze koppeling is een overeenkomst met de Rabobank gesloten.
De koppeling wordt gemaakt door middel van een x509 certificaat met public & private key.
De public key is bekend aan de kant van de Rabobank. De private key blijft geheim aan onze kant.
Beide keys worden bewaard op de server. De locatie hiervan wordt geconfigureerd in
de environment variabelen `PIN_CERTIFICATE_PATH` en `PIN_PRIVATE_KEY_PATH`.
Daarnaast is er een `PIN_URL` en `PIN_CLIENT_ID` configuratie.

De koppeling is gebouwd door [Am. Nederveen](https://csrdelft.nl/profiel/1821) en is gekoppeld
aan de organisatie C.S.R. Delft. Am. Nederveen heeft toegang tot de beheerdersconsole en kan hier als dit
nodig is ook toegang tot geven. Het certificaat is een self-signed certificaat en is geldig tot
16 november 2032. Mocht de pinmatchtool tegen die tijd nog steeds gebruikt worden, gaat het dus
na deze datum stuk.

## Toekomstbestendigere aanpak

Als in de toekomst gekeken gaat worden naar vervanging van het pinapparaat, kan het lonen
te switchen naar een werkwijze waarbij de pintransactie geïnitieerd wordt door het barsysteem
en pas als bestelling aangemaakt wordt als de transactie daadwerkelijk gelukt is.
De Rabobank heeft hier verschillende mogelijkheden voor, maar hiervoor moet het pinapparaat
wel vervangen worden. Er zou ook gekeken kunnen worden naar het gebruik van een telefoon
als pinapparaat.
