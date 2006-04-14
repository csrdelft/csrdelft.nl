<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.ledenlijstcontent.php
# -------------------------------------------------------------------
#
# Deze pagina heeft zoekfunctionaliteit voor zoeken in de ledenlijst
#
# -------------------------------------------------------------------
# Historie:
# 07-09-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('class.lid.php');

class LedenlijstContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;

	# dit is de inhoud van de velden die eerder gesubmit zijn
	# deze inhoud gaan we ook weer terugzetten in de velden
	# de waarden hier zijn de standaardwaarden
	# N.B. naam staat er niet bij, die wordt altijd getoond!
	var	$_form = array(
		'wat' => '',
		'waar' => 'voornaam',
		'kolom' => array('adres', 'email', 'telefoon', 'mobiel'),
		'sort' => 'achternaam',
		'moot' => 'alle'
	);
	# zoekresultaten van de zoekfunctie in de lid-klasse worden
	# door het hoofdprogramma opgevraegd en hier in gestopt, zodat
	# we hier allen maar hoeven te doen waarvoor we zijn, nl. content
	# afbeelden
	var $_result = array();

	### public ###

	function LedenlijstContent (&$lid) {
		$this->_lid =& $lid;
	}
	
	# verander de informatie die standaard in het form ingevuld wordt
	function setForm($form) {
		$this->_form = $form;
	}
	# zoekresultaten
	function setResult($result) {
		$this->_result = $result;
	}

	function view() {

		# De ingevulde zoekterm weer afbeelden
		$form_wat = mb_htmlentities($this->_form['wat']);

		print(<<<EOT
<center><span class="kopje2">Ledenlijst</span></center><p>

Op deze pagina kunt u zoeken in het ledenbestand. Er kan gezocht worden op voornaam, achternaam of email-adres.
U kunt kiezen welke kolommen u wilt weergeven in de zoekresultaten, en welke sorteervolgorde moet worden aangehouden.
Daarnaast is er de mogelijkheid om de complete ledenlijst weer te geven door te zoeken zonder een zoekterm op te geven.
Door op de naam van een lid te klikken kunt u naar de Profiel-pagina  gaan van het genoemde lid. Op
deze pagina is een volledig overzicht van de gegevens te lezen.
<p>

<form action="{$_SERVER['PHP_SELF']}" method="POST">
<input type="hidden" name="a" value="zoek">
Zoek <input type="text" name="wat" class="tekst" style="width:100px;" value="{$form_wat}">
in <select name="waar" class="tekst">
EOT
		);

		# We definieren voor elk veld een 'kolomtitel' die gebruikt wordt boven de kolommen
		# in de zoekresultaten en in de keuzelijstjes voor zoek in, sorteren op etc...
		$kolomtitel = array(
			'nickname' => 'Bijnaam',
			'naam' => 'Naam',
			'voornaam' => 'Voornaam',
			'achternaam' => 'Achternaam',
			'email' => 'Email',
			'kring' => 'Kring',
			'adres' => 'Adres',
			'telefoon' => 'Telefoon',
			'mobiel' => 'Pauper',
			'icq' => 'ICQ',
			'msn' => 'MSN',
			'skype' => 'Skype',
			'uid' => 'Lidnr'
		);
		
		# de velden die we presenteren om in te kunnen zoeken
		$zoek_in_waar = array('nickname','voornaam','achternaam','adres','telefoon','mobiel','email','kring');
		
		foreach ($zoek_in_waar as $veld) {
			print("<option value=\"{$veld}\"");
			if ($this->_form['waar'] == $veld) print(" selected");
			print(">{$kolomtitel[$veld]}</option>\n");
		}
		print("</select>, uit moot:\n<select name=\"moot\" class=\"tekst\">");
		# moten zijn nogal hard-coded, maar ik denk dat het makkelijker is aan te passen
		# in de code als het aantal ooit nog veranderd ipv het dynamisch te gaan maken ofzo
		$zoek_in_moten = array('alle','1','2','3','4');
		foreach ($zoek_in_moten as $veld) {
			print("<option value=\"{$veld}\"");
			if ($this->_form['moot'] == $veld) print(" selected");
			print(">{$veld}</option>\n");
		}
		print("</select>, sorteer daarbij op:\n<select name=\"sort\" class=\"tekst\">");
		
		# de velden waarop de uitvoer geselecteerd kan worden
		$zoek_sort = array('uid','voornaam','achternaam','email','adres','telefoon','mobiel');

		foreach ($zoek_sort as $veld) {
			print("<option value=\"{$veld}\"");
			if ($this->_form['sort'] == $veld) print(" selected");
			print(">{$kolomtitel[$veld]}</option>\n");
		}


		print(<<<EOT
</select>
<input type="submit" class="tekst" name="fu" value=" Zoek! ">

<br /><br />
Laat de volgende kolommen zien:<br />
<table width="100%" border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">

<tr>
EOT
		);

		# zo, en nu de velden die we kunnen tonen in de resultaten
		$laat_zien = array('uid','nickname','email','adres','telefoon','mobiel','icq','msn','skype');
		
		# tralala zorg dat er een even aantal elementen in staat
		if (count($laat_zien)%2 != 0) array_push($laat_zien, false);

		# itereren kun je leren, vakjes afbeelden 2 onder elkaar
		$i = 0;
		foreach ($laat_zien as $veld) {
			# bovenste veld
			if ($i%2 == 0) print("<td valign=\"top\">\n");
			if ($veld !== false) {
				printf('<input type="checkbox" name="kolom[]" value="%s"', $veld);
				if (in_array($veld, $this->_form['kolom'])) print(" checked");
				print(">{$kolomtitel[$veld]}\n");
				if ($i%2 == 0) print("<br />");
				print("\n");
			}
			if ($i%2 == 1) print("</td>\n");
			$i++;
		}

		# afsluiten form
		print(<<<EOT
</tr>
</table>
</form>
EOT
		);
		
		if (count($this->_result) > 0) {
			print(<<<EOT
<table width="100%" border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">
<tr><td class="kopje2" valign="top">Naam</td>
EOT
			);
			foreach ($this->_form['kolom'] as $kolom){
				echo "<td class=\"kopje2\" valign=\"top\">{$kolomtitel[$kolom]}</td>";
			}
			echo '</tr>';

			foreach ($this->_result as $lid) {
				//naam als link naar profiel weergeven.
				echo '<tr><td valign="top"><a href="/leden/profiel.php?uid='.htmlspecialchars($lid['uid']).'">';
				echo mb_htmlentities(str_replace('  ', ' ',implode(' ',array($lid['voornaam'],$lid['tussenvoegsel'],$lid['achternaam'])))).'</a></td>';
				foreach ($this->_form['kolom'] as $kolom) {
					echo '<td valign="top">';
					if($kolom == 'adres'){ 
						echo mb_htmlentities(str_replace('  ', ' ',implode(' ',array($lid['adres'],$lid['postcode'],$lid['woonplaats']))));
					}else{
						echo mb_htmlentities($lid[$kolom]);
					}
					echo '</td>';
				}//einde foreach kolom
				echo '</tr>';
			}//einde foreach lid
			echo'</table>';
		}//einde if isset post
		echo '<br clear="all">';
	}//einde functie view
}//einde classe LedenLijstContent

?>
