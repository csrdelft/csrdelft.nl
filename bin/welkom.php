#!/usr/bin/php5
<?php
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccountModel;

chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';

# Scriptje om voor sjaars een wachtwoord te genereren en dat toe te mailen.
# Vergeet niet voor gebruik hieronder het jaar aan te passen.
$jaar = '18';

foreach (ProfielModel::instance()->find('status = ? AND uid LIKE ?', array(LidStatus::Noviet, sprintf("%s%%", $jaar))) as $profiel) {
    $url = CSR_ROOT . '/wachtwoord/aanvragen';
    $tekst = <<<TEXT
Beste noviet {$profiel->voornaam},

Bij je lidmaatschap van C.S.R. hoort ook de mogelijkheid om in te loggen op de C.S.R.-webstek.

Via de webstek kun je onder andere:
- Berichten lezen en plaatsen op het forum
- Berichten plaatsen in de C.S.R.-courant, die wekelijks aan alle leden wordt verzonden
- Gegevens van andere leden opzoeken
- Aan/afmelden voor de maaltijd, hierover het volgende:
Je hebt een abonnement op de donderdagmaaltijd in Confide. Dit om jullie te stimuleren deel te nemen aan C.S.R. activiteiten en te integreren met leden. Als je inlogt op de webstek zie je meteen op de hoofdpagina een blokje waarin je jezelf kunt af- of aanmelden voor de eerstvolgende maaltijd.
Kun je niet op een maaltijd aanwezig zijn, meld je dan af op de webstek, voor omstreeks 15:00 op de dag van de maaltijd, want na dat tijdstip beginnen de koks met koken en wordt er op jou gerekend met boodschappen doen. Als je er na 15:00 achter komt dat je juist wel of juist niet aanwezig kunt zijn, bel dan even de koks of bel Confide, met een goede reden kan je dan eventueel doorgestreept of toegevoegd worden op de maaltijdlijst.
Waarom is dit belangrijk? Omdat een maaltijd €3,50 kost en als je jezelf vergeet af te melden en niet komt, dit bedrag toch van je CiviSaldo wordt afgeschreven. Het is goed om naar maaltijden te gaan, maar als je niet kunt, meld je dan af! Dat scheelt je pieken.

Gebruik je lidnummer om in te loggen op de webstek: {$profiel->uid}
Gebruik de eerste keer de [url={$url}]wachtwoord aanvragen[/url] functie om je eigen wachtwoord in te stellen.
Nadat je bent ingelogd kun een bijnaam instellen die je in plaats van je lidnummer kunt gebruiken om in te loggen.

Wanneer je problemen hebt met inloggen, of andere vragen over de webstek, kun je terecht bij de PubCie.
Stuur dan een e-mail: pubcie@csrdelft.nl

Met amicale groet,

Job Bakker,
h.t. PubCie-Praeses der Civitas Studiosorum Reformatorum
TEXT;
    $mail = new Mail(array($profiel->email => $profiel->voornaam), 'Inloggegevens C.S.R.-webstek', $tekst);
    $mail->addBcc(array('pubcie@csrdelft.nl' => 'PubCie C.S.R.'));
    $mail->send();

	if (!AccountModel::existsUid($profiel->uid)) {
		// Maak een account aan voor deze noviet
		AccountModel::instance()->maakAccount($profiel->uid);
	}

    echo $profiel->email . " SEND!\n";
}
