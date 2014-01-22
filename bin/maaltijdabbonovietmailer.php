#!/usr/bin/php
<?php
# Scriptje om voor sjaars een wachtwoord te genereren en dat toe te mailen.
# Vergeet niet voor gebruik hieronder het jaar aan te passen.

$jaar = '11';

require_once('configuratie.include.php');

$result = $db->select("SELECT * FROM `lid` WHERE status = 'S_NOVIET'");
if ($result !== false and $db->numRows($result) > 0) {
	while ($sjaars = $db->next($result)){
		
		
		$tekst = <<<EOD
			Lieve novieten,

			Zoals jullie misschien tijdens het novitiaat al hebben gehoord hebben jullie een maaltijd abonnement op de donderdagmaaltijd bij C.S.R. voor de komende tijd. Dit om jullie te stimuleren deel te nemen aan C.S.R. activiteiten en te integreren met leden. Als je inlogt op de webstek zie je meteen op de hoofdpagina een grijs blokje waarin je jezelf kunt af- of aanmelden voor de eerstvolgende maaltijd. Dit kan ook op de pagina http://csrdelft.nl/maaltijden .

			Kun je niet op een maaltijd aanwezig zijn, meld je dan af op de webstek, voor 15:00 op de dag dat de maaltijd gegeven wordt, na dat tijdstip beginnen de koks namelijk met koken en wordt er op jou gerekend met boodschappen doen. Als je er na 15:00 achter komt dat je niet aanwezig kunt zijn, bel dan even de koks of bel Confide, met een goede reden kan je dan eventueel doorgestreept worden van de maaltijdlijst.

			Waarom is dit belangrijk? Omdat een maaltijd €3,- kost en als je jezelf vergeet af te melden en niet komt, dit bedrag toch van je maalcie-saldo wordt afgeschreven. Het is goed om naar maaltijden te gaan, maar als je niet kunt, meld je dan af! Dat scheelt je pieken.

			Voor verdere vragen of opmerkingen of je maalcie-saldo kun je mij altijd mailen op maalcief@csrdelft.nl

			Tot op de maaltijden!

			Benjamin Komen
			o.t. Maalcie fiscus
EOD;
		$nanonovieten = array(1114,1154,1155,1133,1103,1148,1143,1125,1144,1159,1163,1165);
		if((in_array($sjaars['uid'], $nanonovieten))){
			mail ($sjaars['email'],"Maaltijdabbonement C.S.R.",$tekst,"From: Maalcie Fiscus C.S.R. Delft <maalcie-fiscus@csrdelft.nl>\nContent-Type: text/plain; charset=utf-8\nBcc: pubcie@csrdelft.nl, maalcie-fiscus@csrdelft.nl");
			echo $sjaars['email']."\n";
		}
	}
}

?>
