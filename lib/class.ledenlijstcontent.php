<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.ledenlijstcontent.php
# -------------------------------------------------------------------
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
	var $_stati;

	### public ###

	function LedenlijstContent (&$lid, $status=false) {
		$this->_lid =& $lid;
		if($status===false){
			//zoeken in leden
			$this->_stati=array('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL');
		}else{
			$this->_stati=$status;	
		}
	}

	function view() {
		$aStatus=$this->_stati;
		
		# we gaan kijken of er een zoek-opdracht is gegeven
		# zo ja, dan gaan we die straks uitvoeren, en zetten we de ingevulde waarden ook weer
		# terug in de invulvelden
		
		$form = array();
		
		if (isset($_POST['a']) and $_POST['a'] == 'zoek') {
			# er is een zoekopdracht opgegeven, we gaan nu de parameters bekijken
			# eerst de zoekterm ophalen
			if (isset($_POST['wat'])) $form['wat'] = $_POST['wat'];
			# als 'wat' leeg is, dan wordt er naar alle leden gezocht
			else $form['wat'] = '';
			
			# in welke kolom van de tabel gezocht wordt...
			$kolommen = array('nickname', 'voornaam', 'achternaam', 'email');
			if (isset($_POST['waar']) and in_array($_POST['waar'],$kolommen))
				$form['waar'] = $_POST['waar'];
			# als er niets geldigs is opgegeven, dan op voornaam zoeken
			else $form['waar'] = 'voornaam';
			
			# kolommen die afgebeeld kunnen worden
			$kolommen = array('email', 'adres', 'telefoon', 'mobiel', 'icq', 'msn', 'skype', 'uid', 'nickname');
			$form['kolom'] = array();
			# kijken of er geldige kolommen zijn opgegeven
			if (isset($_POST['kolom']) AND is_array($_POST['kolom']) AND count($_POST['kolom']) > 0)
				$form['kolom'] = array_intersect($_POST['kolom'], $kolommen);
			# als er geen enkele geldige waarde was zelf een voorstel doen
			if (count($form['kolom']) == 0) $form['kolom'] = array('naam', 'adres', 'email', 'telefoon', 'mobiel');
			
			# kolom waarop gesorteerd wordt
			$kolommen = array('uid', 'voornaam', 'achternaam', 'adres', 'email', 'telefoon', 'pauper');
			if (isset($_POST['sort']) and in_array($_POST['sort'],$kolommen))
				$form['sort'] = $_POST['sort'];
			# als er niets geldigs is opgegeven, dan op achternaam zoeken
			else $form['sort'] = 'achternaam';
		} else {
			# standaardwaarden die in de invulvelden komen
			$form = array(
				'wat' => '',
				'waar' => 'voornaam',
				'kolom' => array('naam', 'adres', 'email', 'telefoon', 'mobiel'),
				'sort' => 'achternaam'
			);
		}


?>
<center><span class="kopje2">Ledenlijst</span></center><p>

Op deze pagina kunt u zoeken in het ledenbestand. Er kan gezocht worden op voornaam, achternaam of email-adres.
U kunt kiezen welke kolommen u wilt weergeven in de zoekresultaten, en welke sorteervolgorde moet worden aangehouden.
Daarnaast is er de mogelijkheid om de complete ledenlijst weer te geven door te zoeken zonder een zoekterm op te geven.
Door op de naam van een lid te klikken kunt u naar de Profiel-pagina  gaan van het genoemde lid. Op
deze pagina is een volledig overzicht van de gegevens te lezen.
<p>

<form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
<input type="hidden" name="a" value="zoek">
Zoek <input type="text" name="wat" class="tekst" style="width:100px;" value="<?=htmlspecialchars($form['wat'])?>">
in <select name="waar" class="tekst">
<option value="nickname"<? if ($form['waar'] == 'nickname') echo " selected"; ?>>Bijnaam</option>
<option value="voornaam"<? if ($form['waar'] == 'voornaam') echo " selected"; ?>>Voornaam</option>
<option value="achternaam"<? if ($form['waar'] == 'achternaam') echo " selected"; ?>>Achternaam</option>
<option value="email"<? if ($form['waar'] == 'email') echo " selected"; ?>>Email-adres</option>
</select>, sorteer daarbij op:
<select name="sort" class="tekst">
<option value="uid"<? if ($form['sort'] == 'uid') echo " selected"; ?>>Lidnummer</option>
<option value="voornaam"<? if ($form['sort'] == 'voornaam') echo " selected"; ?>>Voornaam</option>
<option value="achternaam"<? if ($form['sort'] == 'achternaam') echo " selected"; ?>>Achternaam</option>
<option value="email"<? if ($form['sort'] == 'email') echo " selected"; ?>>Email-adres</option>
<option value="adres"<? if ($form['sort'] == 'adres') echo " selected"; ?>>Adres</option>
<option value="telefoon"<? if ($form['sort'] == 'telefoon') echo " selected"; ?>>Telefoon</option>
<option value="mobiel"<? if ($form['sort'] == 'mobiel') echo " selected"; ?>>Pauper</option>
</select>
<input type="submit" class="tekst" name="fu" value=" Zoek! ">

<br /><br />
Laat de volgende kolommen zien:<br />
<table width="100%" border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">

<tr>
<td valign="top">
<input type="checkbox" name="kolom[]" value="uid"<? if (in_array('uid', $form['kolom'])) echo " checked"; ?>>Lid-nummer<br />
</td>

<td>
<input type="checkbox" name="kolom[]" value="nickname"<? if (in_array('nickname', $form['kolom'])) echo " checked"; ?>>Bijnaam<br />
<input type="checkbox" name="kolom[]" value="email"<? if (in_array('email', $form['kolom'])) echo " checked"; ?>>Email
</td>

<td>
<input type="checkbox" name="kolom[]" value="adres"<? if (in_array('adres', $form['kolom'])) echo " checked"; ?>>Adres<br />
<input type="checkbox" name="kolom[]" value="telefoon"<? if (in_array('telefoon', $form['kolom'])) echo " checked"; ?>>Telefoon
</td>

<td>
<input type="checkbox" name="kolom[]" value="mobiel"<? if (in_array('mobiel', $form['kolom'])) echo " checked"; ?>>Pauper<br />
<input type="checkbox" name="kolom[]" value="icq"<? if (in_array('icq', $form['kolom'])) echo " checked"; ?>>ICQ
</td>

<td>
<input type="checkbox" name="kolom[]" value="msn"<? if (in_array('msn', $form['kolom'])) echo " checked"; ?>>MSN<br />
<input type="checkbox" name="kolom[]" value="skype"<? if (in_array('skype', $form['kolom'])) echo " checked"; ?>>Skype
</td>

</table>

</form>
<?php
		if (isset($_POST['a']) and $_POST['a'] == 'zoek') {
			$kolomtitel = array(
				'naam' => 'Naam',
				'email' => 'Email',
				'adres' => 'Adres',
				'telefoon' => 'Telefoon',
				'mobiel' => 'Pauper',
				'icq' => 'ICQ',
				'msn' => 'MSN',
				'skype' => 'Skype',
				'uid' => 'Lidnr',
				'nickname' => 'Bijnaam'
			);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">
<tr><td class="kopje2" valign="top">Naam</td>
<?php
			foreach ($form['kolom'] as $kolom){
				echo "<td class=\"kopje2\" valign=\"top\">{$kolomtitel[$kolom]}</td>";
			}
			echo '</tr>';
			$leden = $this->_lid->zoekLeden($form['wat'], $form['waar'], $form['sort'], $aStatus);
			foreach ($leden as $lid) {
				//naam als link naar profiel weergeven.
				echo '<tr><td valign="top"><a href="/leden/profiel.php?uid='.htmlspecialchars($lid['uid']).'">';
				echo mb_htmlentities(str_replace('  ', ' ',implode(' ',array($lid['voornaam'],$lid['tussenvoegsel'],$lid['achternaam'])))).'</a></td>';
				foreach ($form['kolom'] as $kolom) {
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
