#!/usr/bin/php
<?php
# Scriptje om voor sjaars een wachtwoord te genereren en dat toe te mailen.
# Vergeet niet voor gebruik hieronder het jaar aan te passen.

$jaar = '12';

require_once('configuratie.include.php');

$result = $db->select("SELECT * FROM `lid` WHERE status = 'S_NOVIET'");
if ($result !== false and $db->numRows($result) > 0) {
	while ($sjaars = $db->next($result)){
		$nanonovieten = array();
		if(!(in_array($sjaars['uid'], $nanonovieten))){
			$tekens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
			$pass = '';
			for($i = 0; $i < 8; $i++) {
				$pass .= substr($tekens,rand(0,strlen($tekens)),1);
			}

			$passwordhash=makepasswd($pass);
			$sQuery="UPDATE lid SET password='".$passwordhash."' WHERE uid='".$sjaars['uid']."' LIMIT 1;";
			$db->query($sQuery);

			//cache resetten.
			LidCache::flushLid($sjaars['uid']);
		
			$tekst = <<<EOD
Beste noviet {$sjaars['voornaam']},

Bij je lidmaatschap van C.S.R. hoort ook de mogelijkheid om in te loggen op de C.S.R.-webstek.

Via de webstek kun je onder andere:
- Berichten lezen en plaatsen op het forum
- Berichten plaatsen in de C.S.R.-courant, die wekelijks aan alle leden wordt verzonden
- Gegevens van andere leden opzoeken

Je hebt een abbonement op de donderdag maaltijd. Als je een donderdag niet kan, kun je je afmelden op de webstek. Doe dit voor de maaltijd sluit, anders sta je toch ingeschreven en kost je dit dus €3. 

Je inloggegevens zijn als volgt:
Lidnummer: {$sjaars['uid']}
Wachtwoord: {$pass}

Nadat je bent ingelogd kun je het wachtwoord veranderen, en een bijnaam instellen die je in plaats van je lidnummer kunt gebruiken om in te loggen.



Wanneer je problemen hebt met inloggen, of andere vragen over de webstek, kun je terecht bij de PubliciteitsCommissie.
Stuur dan een e-mail of kom even langs in ons IRC-kanaal #pubcie (zie Communicatie->IRC), of stuur een e-mail.

Met vriendelijke groet,

Ruben Verboon
h.t. PubCie-Praeses der Civitas Studiosorum Reformatorum
EOD;
				mail ($sjaars['email'],"Inloggegevens C.S.R.-webstek",$tekst,"From: PubliciteitsCommissie C.S.R. Delft <pubcie@csrdelft.nl>\nContent-Type: text/plain; charset=utf-8\nBcc: pubcie@csrdelft.nl");
				echo $sjaars['email']."\n";
		}
	}
}

?>
