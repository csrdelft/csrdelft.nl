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
	# N.B. status staat er ook niet bij, die wordt in view() neergezet
	#      als die nog niet doorgegeven is vanuit het hoofdprogramma
	#      met setForm
	var	$_form = array(
		'wat'    => '',
		'waar'   => 'naam',
		'kolom'  => array('adres', 'email', 'telefoon', 'mobiel'),
		'sort'   => 'achternaam',
		'moot'   => 'alle'
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
	function getTitel(){
		return 'Ledenlijst';
	}
	function viewWaarbenik(){
		echo '<a href="/intern/">Intern</a> &raquo; Ledenlijst';
	}

	function view() {

		# De ingevulde zoekterm weer afbeelden
		$form_wat = mb_htmlentities($this->_form['wat']);

		print(<<<EOT
<h1>Ledenlijst</h1>

Op deze pagina kunt u zoeken in het ledenbestand. Er kan gezocht worden op voornaam, achternaam of email-adres.
U kunt kiezen welke kolommen u wilt weergeven in de zoekresultaten, en welke sorteervolgorde moet worden aangehouden.
Daarnaast is er de mogelijkheid om de complete ledenlijst weer te geven door te zoeken zonder een zoekterm op te geven.
Door op de naam van een lid te klikken kunt u naar de Profiel-pagina  gaan van het genoemde lid. Op
deze pagina is een volledig overzicht van de gegevens te lezen.
<form action="{$_SERVER['PHP_SELF']}" method="post">
<p>
<input type="hidden" name="a" value="zoek" />
Zoek <input type="text" name="wat" style="width:100px;" value="{$form_wat}" />
in <select name="waar">
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
		$zoek_in_waar = array('naam','nickname','voornaam','achternaam','adres','telefoon','mobiel','email','kring');
		
		foreach ($zoek_in_waar as $veld) {
			echo '<option value="'.$veld.'"';
			if ($this->_form['waar'] == $veld) echo ' selected="selected"';
			echo '>'.$kolomtitel[$veld].'</option>';
		}
		echo '</select>, moot:<select name="moot">';
		# moten zijn nogal hard-coded, maar ik denk dat het makkelijker is aan te passen
		# in de code als het aantal ooit nog veranderd ipv het dynamisch te gaan maken ofzo
		$zoek_in_moten = array('alle','1','2','3','4');
		foreach ($zoek_in_moten as $veld) {
			echo '<option value="'.$veld.'"';
			if ($this->_form['moot'] == $veld) echo ' selected="selected"';
			echo '>'.$veld.'</option>';
		}
		
		# als ingelogde persoon leesrechten heeft op leden + oudleden maken we een extra
		# keuzelijstje. zoeken in leden, oudleden, of allebei tegelijk.
		if ($this->_lid->hasPermission('P_LEDEN_READ') and $this->_lid->hasPermission('P_OUDLEDEN_READ')) {
			echo '</select>, status:<select name="status">';

			$zoek_in_type = array('(oud)?leden','leden','oudleden');

			if (!isset($this->_form['status'])) {
				# voor de standaard-optie kijken we naar de status van de ingelogde persoon
				$mystatus = $this->_lid->getStatus();
				if ($mystatus == 'S_OUDLID') {
					$this->_form['status'] = 'oudleden';
				} elseif (in_array($mystatus, array('S_LID','S_GASTLID','S_NOVIET','S_KRINGEL'))) {
					$this->_form['status'] = 'leden';
				} else {
					$this->_form['status'] = '(oud)?leden';
				}
			}
			foreach ($zoek_in_type as $veld) {
				echo '<option value="'.$veld.'"';
				if ($this->_form['status'] == $veld) echo ' selected="selected"';
				echo '>'.$veld.'</option>';
			}
		}		
		
		echo '</select>, sorteer op:<select name="sort" >';
		
		# de velden waarop de uitvoer geselecteerd kan worden
		$zoek_sort = array('uid','voornaam','achternaam','email','adres','telefoon','mobiel');

		foreach ($zoek_sort as $veld) {
			echo '<option value="'.$veld.'"';
			if ($this->_form['sort'] == $veld) echo ' selected="selected"';
			echo '>'.$kolomtitel[$veld].'</option>';
		}
		echo '</select> 
			<input type="submit" name="fu" value=" Zoek! " /><br /><br />
			Laat de volgende kolommen zien:<br /></p><table style="width: 100%"><tr>';
		


		# zo, en nu de velden die we kunnen tonen in de resultaten
		$laat_zien = array('uid','nickname','email','adres','telefoon','mobiel','icq','msn','skype');
		
		# tralala zorg dat er een even aantal elementen in staat
		if (count($laat_zien)%2 != 0) array_push($laat_zien, false);

		# itereren kun je leren, vakjes afbeelden 2 onder elkaar
		$i = 0;
		foreach ($laat_zien as $veld) {
			# bovenste veld
			if ($i%2 == 0) print('<td>');
			if ($veld !== false) {
				printf('<input type="checkbox" name="kolom[]" value="%s"', $veld);
				if (in_array($veld, $this->_form['kolom'])) echo ' checked="checked"';
				echo ' />'.$kolomtitel[$veld];
				if ($i%2 == 0) echo '<br />';
				print("\n");
			}
			if ($i%2 == 1) echo '</td>';
			$i++;
		}

		# afsluiten form
		echo '</tr></table></form><br />';
		
		if(count($this->_result) > 0) {
			//zoekresultatentabel met eerst de kopjes		
			echo '<table style="width: 100%"><tr>';
			if($this->_lid->hasPermission('P_LEDEN_MOD')){ echo '<td>&nbsp;</td>'; }
			echo '<td><strong>Naam</strong></td>';
			foreach ($this->_form['kolom'] as $kolom){
				echo '<td><strong>'.$kolomtitel[$kolom].'</strong></td>';
			}
			echo '</tr>';
			//en de resultaten...
			foreach ($this->_result as $lid) {
				$uid=htmlspecialchars($lid['uid']);
				echo '<tr>';
				
				if($this->_lid->hasPermission('P_LEDEN_MOD')){
					echo '<td><a href="/intern/profiel.php?uid='.$uid.'&amp;a=edit">[b]</a>&nbsp;';
				}
				//naam als link naar profiel weergeven.
				echo '<td><a href="/intern/profiel/'.$uid.'">';
				echo mb_htmlentities(naam($lid['voornaam'], $lid['achternaam'], $lid['tussenvoegsel'])).'</a></td>';
				//de rest van de kolommen.
				foreach ($this->_form['kolom'] as $kolom) {
					echo '<td>';
					if($kolom == 'adres'){ 
						echo mb_htmlentities($lid['adres'].' '.$lid['postcode'].' '.$lid['woonplaats']);
					}else{
						echo mb_htmlentities($lid[$kolom]);
					}
					echo '</td>';
				}//einde foreach kolom
				echo '</tr>';
			}//einde foreach lid
			echo'</table>';
		}else{
			if(trim($form_wat)!='') echo '<br />Uw zoekterm heeft niets gevonden. Probeert u het nog eens.';
		}//einde if count($this->_result)
		echo '<br />';
	}//einde functie view
}//einde classe LedenLijstContent

?>
