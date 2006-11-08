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
require_once ('class.eetplan.php');

class EetplanContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_eetplan;

	### public ###

	function EetplanContent (&$eetplan) {
		$this->_eetplan =& $eetplan;
	}
	function getTitel(){
		return 'Eetplan';
	}
	function viewWaarbenik(){ 
		
		echo '<a href="/intern/">Intern</a> &raquo; Eetplan';
	}
	function viewEetplanVoorPheut($iPheutID){
		//huizen voor een feut tonen
		$aEetplan=$this->_eetplan->getEetplanVoorPheut($iPheutID);
		if($aEetplan===false){
			echo '<h1>Ongeldig pheutID</h1>';
		}else{
			$aPheutNaam=$this->_eetplan->getPheutNaam($iPheutID);
			echo '<h2><a class="forumGrootlink" href="/intern/eetplan/">Eetplan</a> &raquo; voor '.mb_htmlentities($aPheutNaam['naam']).'</h2>
				<a href="/intern/profiel/'.$iPheutID.'">Naar profiel van '.mb_htmlentities($aPheutNaam['naam']).'</a><br /><br />';
			echo '<table class="hoktable">
				<tr><th style="width: 150px">Avond</th><th style="width: 200px">Huis</th></tr>';
			foreach($aEetplan as $aEetplanData){
				echo '
					<tr>
						<td >'.$this->_eetplan->getDatum($aEetplanData['avond']).'</td>
						<td><a href="/intern/eetplan/huis/'.$aEetplanData['huisID'].'"><strong>'.mb_htmlentities($aEetplanData['huisnaam']).'</strong></a><br />
							'.mb_htmlentities($aEetplanData['huisadres']).' | '.mb_htmlentities($aEetplanData['telefoon']).'
						</td></tr>';
			}
			echo '</table>';
		}
	}
	function viewEetplanVoorHuis($iHuisID){
		//feuten voor een huis tonen
		$aEetplan=$this->_eetplan->getEetplanVoorHuis($iHuisID);
		
		if($aEetplan===false){
			echo '<h1>Ongeldig huisID</h1>';
		}else{
			$sUitvoer='<table>
				<tr>
				<th style="width: 150px">Avond</td>
				<th style="width: 200px">Pheut</td>
				<th>Telefoon</td>
				<th>Mobiel</td>
				</tr>';
			$iHuidigAvond=0; 
			foreach($aEetplan as $aEetplanData){
				$sUitvoer.='
					<tr>
						<td>';
				if($aEetplanData['avond']==$iHuidigAvond){
					$sUitvoer.='&nbsp;';
				}else{
					$sUitvoer.=$this->_eetplan->getDatum($aEetplanData['avond']);
					$iHuidigAvond=$aEetplanData['avond'];
				}
				$aPheutNaam=$this->_eetplan->getPheutNaam($aEetplanData['pheut']);
				$sUitvoer.='</td>
					<td><strong><a href="/intern/eetplan/sjaars/'.$aEetplanData['pheut'].'">'.mb_htmlentities($aPheutNaam['naam']).'</a></strong><br /></td>
					<td>'.mb_htmlentities($aPheutNaam['telefoon']).'</td>
					<td>'.mb_htmlentities($aPheutNaam['mobiel']).'</td>
					</tr>';
			
			}
			$sUitvoer.='</table>';
			echo '<h2><a class="forumGrootlink"href="/intern/eetplan/">Eetplan</a> &raquo; voor '.mb_htmlentities($aEetplanData['huisnaam']).'</h2>
				'.mb_htmlentities($aEetplanData['huisadres']).' <br /> 
				Telefoon: '.mb_htmlentities($aEetplanData['telefoon']).'<br />
				Ga naar <a href="/groepen/woonoorden.php">woonoordenpagina</a><br /><br />'.
				$sUitvoer;
		}
	}
	function viewEetplan($aEetplan){
		//weergeven
		echo '<h1>Eetplan overzicht</h1>
		<strong style="color: darkred; font-size: 18px; font-weight: bold;">LET OP: Van eerstejaers die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen koken op het huis waarbij zij gefaeld hebben.</strong><br /><br />
			<table style="width: 100%" >
			<tr><th style="width: 200px;">Pheut/Avond</td>';
		//kopjes voor tabel
		for($iTeller=1;$iTeller<=8;$iTeller++){
			echo '<th>'.$this->_eetplan->getDatum($iTeller).'</th>';
		}	
		echo '</tr>';
		
		foreach($aEetplan as $aEetplanVoorPheut){
			echo '<tr><td><a href="/intern/eetplan/sjaars/'.$aEetplanVoorPheut[0]['uid'].'">'.mb_htmlentities($aEetplanVoorPheut[0]['naam']).'</a></td>';
			for($iTeller=1;$iTeller<=8;$iTeller++){
				echo '<td><a href="/intern/eetplan/huis/'.$aEetplanVoorPheut[$iTeller].'">'.
					mb_htmlentities($aEetplanVoorPheut[$iTeller]).
					'</a></td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		//nog even een huizentabel erachteraan
		$aHuizenArray=$this->_eetplan->getHuizen();
		echo '<h1>Huizen met hun nummers:</h1>
			<table style="width: 100%;"> 
				<tr><th>huisID</th><th>Naam</th><th>Adres</th><th>Telefoon</th></tr>';
		foreach($aHuizenArray as $aHuis){
			echo '<tr>
				<td><a href="/intern/eetplan/huis/'.$aHuis['huisID'].'">'.$aHuis['huisID'].'</a></td>
				<td><a href="/intern/eetplan/huis/'.$aHuis['huisID'].'">'.mb_htmlentities($aHuis['huisNaam']).'</a></td>
				<td>'.$aHuis['adres'].'</td>
				<td>'.$aHuis['telefoon'].'</td></tr>';
		}
		echo '</table>';
	}
	function view() {
		//kijken of er een pheut of een huis gevraagd wordt, of een overzicht.
		if(isset($_GET['pheutID'])){
			//eetplanavonden voor een pheut tonen
			$iPheutID='0'.(int)$_GET['pheutID'];
			$this->viewEetplanVoorPheut($iPheutID);
		}elseif(isset($_GET['huisID'])){
			//pheuten voor een huis tonen
			$iHuisID=(int)$_GET['huisID'];
			$this->viewEetplanVoorHuis($iHuisID);
		}else{
			//standaard actie, gewoon overzicht tonen.
			$aEetplan=$this->_eetplan->getEetplan();
			$this->viewEetplan($aEetplan);
		}
	}
}

?>
