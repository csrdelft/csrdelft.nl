#!/usr/bin/php5
<?php
# Scriptje om voor sjaars een wachtwoord te genereren en dat toe te mailen.
# Vergeet niet voor gebruik hieronder het jaar aan te passen.

$jaar = '14';

session_id('welkom-cli');

chdir(dirname(__FILE__) . '/../lib/');

require_once 'configuratie.include.php';
require_once 'MVC/model/entity/Mail.class.php';

$db = MijnSqli::instance();

$result = $db->select("SELECT * FROM `lid` WHERE status = 'S_NOVIET'");
if ($result !== false and $db->numRows($result) > 0) {
	while ($sjaars = $db->next($result)) {
		$nanonovieten = array();
		if (!in_array($sjaars['uid'], $nanonovieten)) {
			$url = CSR_ROOT . '/wachtwoord/vergeten';
			$tekst = <<<EOD
Beste noviet {$sjaars['voornaam']},

Bij je lidmaatschap van C.S.R. hoort ook de mogelijkheid om in te loggen op de C.S.R.-webstek.

Via de webstek kun je onder andere:
- Berichten lezen en plaatsen op het forum
- Berichten plaatsen in de C.S.R.-courant, die wekelijks aan alle leden wordt verzonden
- Gegevens van andere leden opzoeken

Je hebt een abbonement op de donderdagmaaltijd in Confide. Dit om jullie te stimuleren deel te nemen aan C.S.R. activiteiten en te integreren met leden. Als je inlogt op de webstek zie je meteen op de hoofdpagina een blokje waarin je jezelf kunt af- of aanmelden voor de eerstvolgende maaltijd.
Kun je niet op een maaltijd aanwezig zijn, meld je dan af op de webstek, voor omstreeks 15:00 op de dag van de maaltijd, want na dat tijdstip beginnen de koks met koken en wordt er op jou gerekend met boodschappen doen. Als je er na 15:00 achter komt dat je juist wel of juist niet aanwezig kunt zijn, bel dan even de koks of bel Confide, met een goede reden kan je dan eventueel doorgestreept of toegevoegd worden op de maaltijdlijst.
Waarom is dit belangrijk? Omdat een maaltijd €3,- kost en als je jezelf vergeet af te melden en niet komt, dit bedrag toch van je maalcie-saldo wordt afgeschreven. Het is goed om naar maaltijden te gaan, maar als je niet kunt, meld je dan af! Dat scheelt je pieken.
Voor verdere vragen of opmerkingen of je maalcie-saldo je kun de MaalCie fiscus altijd mailen op maalcief@csrdelft.nl

Gebruik je lidnummer om in te loggen op de webstek: {$sjaars['uid']}
Gebruik de eerste keer de [url={$url}]wachtwoord vergeten[/url] functie om je eigen wachtwoord in te stellen.
Nadat je bent ingelogd kun een bijnaam instellen die je in plaats van je lidnummer kunt gebruiken om in te loggen.

Als de sneeuw je computer traag maakt kan je deze onder instellingen vastzetten of uitzetten.

Wanneer je problemen hebt met inloggen, of andere vragen over de webstek, kun je terecht bij de PubliciteitsCommissie.
Stuur dan een e-mail.

Met vriendelijke groet,

Gerben Oolbekkink
h.t. PubCie-Praeses der Civitas Studiosorum Reformatorum
EOD;
			$mail = new Mail(array($sjaars['email'] => $sjaars['voornaam']), 'Inloggegevens C.S.R.-webstek', $tekst);
			$mail->addBcc(array('pubcie@csrdelft.nl' => 'PubCie C.S.R.'));
			$mail->send();
			echo $sjaars['email'] . "\n";
		}
	}
}
