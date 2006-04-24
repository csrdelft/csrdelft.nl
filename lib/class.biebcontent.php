<?php

/*
 * Bezinning op waarom de bestanden zo ingedeeld zijn:
 * Volgens mij is het nu zo:
 * 		class.bieb.php			database-functies voor bieb, gebruikt door biebcontent
 * 		class.biebcontent.php	weergave-functies voor bieb
 *		?.php					wijzig-functies voor bieb, + lancering biebcontent
 *
 * Klopt dat?
 * Is het niet onhandig dat alle weergave-functies in biebcontent staan? Ik zie ze misschien liever gegroepeerd
 */

class BiebContent {
	var $_bieb, $_action, $_taalKort;
	
	function BiebContent($bieb, $action = "") {
		$this->_bieb = $bieb;
		$this->_action = $action;
		# todo: misschien kan dit weg en kan de taal gewoon helemaal weergegeven worden. 
		$this->_taalKort = Array(
			'Nederlands' 	=> 	'nl',
			'Engels'		=>	'en',
			'Duits'			=>	'du',
			'Frans'			=>	'fr',
			'Overig'		=>	'?'
		);
	}
	
	function viewOverzicht() {
		echo "<a href=\"catalogus.php\">Catalogus</a><br>\n";
	}
	
	function catalogusMain() {
		# de aanwezige boeken opvragen
		if (isset($_GET['sorteerOp'])) { $sorteerOp = $_GET['sorteerOp']; }
		else { $sorteerOp = 'auteur'; }
		$boeken = $this->_bieb->getBoekenAanwezig($sorteerOp);
		
		# boeken op scherm gooien
?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">
			<tr>
				<td class="kopje2" valign="top"><a href="?sorteerOp=auteur">Auteur</a>&nbsp;</td>
				<td class="kopje2" valign="top"><a href="?sorteerOp=titel">Titel</a>&nbsp;</td>
				<td class="kopje2" valign="top"><a href="?sorteerOp=categorie">Categorie</a>&nbsp;</td>
				<td class="kopje2" valign="top"><a href="?sorteerOp=taal">Taal</a>&nbsp;</td>
				<td class="kopje2" valign="top"><a href="?sorteerOp=aantal">Aantal</a>&nbsp;</td>
				<td class="kopje2" valign="top">&nbsp;</td>
			</tr>
<?
		for ($i = 0; $i < count($boeken); $i++) {
?>
			<tr>
				<td valign="top"><a href="?action=catalogusViewAuteur&auteur_id=<?=$boeken[$i]['auteur_id']?>"><?=$boeken[$i]['auteur']?></a>&nbsp;</td>
				<td valign="top"><?=$boeken[$i]['titel']?>&nbsp;</td>
				<td valign="top"><?=$boeken[$i]['categorie']?>&nbsp;</td>
				<td valign="top"><?=$this->_taalKort[ $boeken[$i]['taal'] ]?>&nbsp;</td>
				<td valign="top"><?=$boeken[$i]['aantal']?>&nbsp;</td>
				<td valign="top">Lenenlink&nbsp;</td>
			</tr>
<?
		}
?>
		</table>
<?
	}
	
	function catalogusViewAuteur() {
		# de aanwezige boeken opvragen
		if (isset($_GET['sorteerOp'])) { $sorteerOp = $_GET['sorteerOp']; }
		else { $sorteerOp = 'auteur'; }
		$boeken = $this->_bieb->getBoekenAanwezigVanAuteur($_GET['auteur_id'], $sorteerOp);
		
		# boeken op scherm gooien
?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">
			<tr>
				<td class="kopje2" valign="top"><a href="?sorteerOp=auteur">Auteur</a>&nbsp;</td>
				<td class="kopje2" valign="top"><a href="?sorteerOp=titel">Titel</a>&nbsp;</td>
				<td class="kopje2" valign="top"><a href="?sorteerOp=categorie">Categorie</a>&nbsp;</td>
				<td class="kopje2" valign="top"><a href="?sorteerOp=taal">Taal</a>&nbsp;</td>
				<td class="kopje2" valign="top"><a href="?sorteerOp=aantal">Aantal</a>&nbsp;</td>
				<td class="kopje2" valign="top">&nbsp;</td>
			</tr>
<?
		for ($i = 0; $i < count($boeken); $i++) {
?>
			<tr>
				<td valign="top"><a href="?action=catalogusViewAuteur"><?=$boeken[$i]['auteur']?></a>&nbsp;</td>
				<td valign="top"><?=$boeken[$i]['titel']?>&nbsp;</td>
				<td valign="top"><?=$boeken[$i]['categorie']?>&nbsp;</td>
				<td valign="top"><?=$this->_taalKort[ $boeken[$i]['taal'] ]?>&nbsp;</td>
				<td valign="top"><?=$boeken[$i]['aantal']?>&nbsp;</td>
				<td valign="top">Lenenlink&nbsp;</td>
			</tr>
<?
		}
?>
		</table>
<?
	}
	
	function catalogusViewTitel() {
	}
	
	function catalogusViewEigenaar() {
	}
	
	function view() {
		switch($this->_action) {
			case "eigenBoekenMain":
			case "eigenBoekenToevoegen":
			case "eigenBoekenWijzigen":
			case "eigenBoekenVerwijderen":
			case "catalogusMain":
			case "catalogusViewAuteur":
			case "catalogusViewTitel":
			case "catalogusViewEigenaar":
			case "lenenMain":
			case "lenenLeen":
			case "lenenMeldUitgeleend":
			case "lenenMeldTeruggekregen":
			case "lenenMeldTeruggegeven":
				# voer de functie uit waarvan de naam in $this->_action staat
				$functieNaam = $this->_action;
				$this->$functieNaam();
				break;
			
			default:
				$this->viewOverzicht();
				break;
		}
	}
}
?>
