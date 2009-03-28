<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.ledenlijstcontent.php
# -------------------------------------------------------------------


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

	function viewEetplanVoorPheut($uid){
		//huizen voor een feut tonen
		$aEetplan=$this->_eetplan->getEetplanVoorPheut($uid);
		if($aEetplan===false){
			echo '<h1>Ongeldig pheutID</h1>';
		}else{
			$lid=LidCache::getLid($uid);
			echo '<h2><a class="forumGrootlink" href="/actueel/eetplan/">Eetplan</a> &raquo; voor '.$lid->getNaamLink('full', 'plain').'</h2>
				Profiel van '.$lid->getNaamLink('civitas','plain').'<br /><br />';
			echo '<table class="eetplantabel">
				<tr><th style="width: 150px">Avond</th><th style="width: 200px">Huis</th></tr>';
			$row=0;
			foreach($aEetplan as $aEetplanData){
				echo '
					<tr class="kleur'.($row%2).'">
						<td >'.$this->_eetplan->getDatum($aEetplanData['avond']).'</td>
						<td><a href="/actueel/eetplan/huis/'.$aEetplanData['huisID'].'"><strong>'.mb_htmlentities($aEetplanData['huisnaam']).'</strong></a><br />
							'.mb_htmlentities($aEetplanData['huisadres']).' | '.mb_htmlentities($aEetplanData['telefoon']).'
						</td></tr>';
				$row++;
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
			$sUitvoer='<table class="eetplantabel">
				<tr>
				<th style="width: 150px">Avond</th>
				<th style="width: 200px">Pheut</th>
				<th>Telefoon</th>
				<th>Mobiel</th>
				<th>Eetwens</th>
				</tr>';
			$iHuidigAvond=0;
			$row=0;
			foreach($aEetplan as $aEetplanData){
				if($aEetplanData['avond']==$iHuidigAvond){
					$ertussen='&nbsp;';
				}else{
					$ertussen=$this->_eetplan->getDatum($aEetplanData['avond']);
					$iHuidigAvond=$aEetplanData['avond'];
					$row++;
				}
				$sUitvoer.='
					<tr class="kleur'.($row%2).'">
						<td>'.$ertussen;

				$pheutnaam=$this->_eetplan->getPheutNaam($aEetplanData['pheut']);
				$sUitvoer.='</td>
					<td>'.$pheutnaam.'<br /></td>
					<td>'.mb_htmlentities($aEetplanData['telefoon']).'</td>
					<td>'.mb_htmlentities($aEetplanData['mobiel']).'</td>
					<td>'.mb_htmlentities($aEetplanData['eetwens']).'</td>
					</tr>';

			}
			$sUitvoer.='</table>';
			echo '<h2><a class="forumGrootlink"href="/actueel/eetplan/">Eetplan</a> &raquo; voor '.mb_htmlentities($aEetplanData['huisnaam']).'</h2>
				'.mb_htmlentities($aEetplanData['huisadres']).' <br />
				Telefoon: '.mb_htmlentities($aEetplanData['telefoon']).'<br />
				Ga naar <a href="/actueel/groepen/Woonoorden/">woonoordenpagina</a><br /><br />'.
				$sUitvoer;
		}
	}
	function viewEetplan($aEetplan){
		$aHuizenArray=$this->_eetplan->getHuizen();
		//weergeven
		echo '
		<strong id="eetplanLETOP">LET OP: Van eerstejaers die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen koken op het huis waarbij zij gefaeld hebben.</strong><br /><br />
			<table class="eetplantabel">
			<tr><th style="width: 200px;">Pheut/Avond</td>';
		//kopjes voor tabel
		for($iTeller=1;$iTeller<=8;$iTeller++){
			echo '<th class="huis">'.$this->_eetplan->getDatum($iTeller).'</th>';
		}
		echo '</tr>';
		$row=0;
		foreach($aEetplan as $aEetplanVoorPheut){


			echo '<tr class="kleur'.($row%2).'"><td><a href="/actueel/eetplan/sjaars/'.$aEetplanVoorPheut[0]['uid'].'">'.$aEetplanVoorPheut[0]['naam'].'</a></td>';
			for($iTeller=1;$iTeller<=8;$iTeller++){
				$huisnaam=$aHuizenArray[$aEetplanVoorPheut[$iTeller]-1]['huisNaam'];
				$huisnaam=str_replace(array('Huize ', 'De ', '-', ' '), '', $huisnaam);
				$huisnaam=substr($huisnaam, 0,9);

				echo '<td class="huis"><a href="/actueel/eetplan/huis/'.$aEetplanVoorPheut[$iTeller].'">'.
					mb_htmlentities($huisnaam).
					'</a></td>';
			}
			echo '</tr>';
			$row++;
		}
		echo '</table>';
		//nog even een huizentabel erachteraan

		echo '<br /><h1>Huizen met hun nummers:</h1>
			<table class="eetplantabel">
				<tr><th>Naam</th><th>Adres</th><th>Telefoon</th></tr>';

		foreach($aHuizenArray as $aHuis){
			echo '<tr class="kleur'.($row%2).'">
				<td><a href="/actueel/eetplan/huis/'.$aHuis['huisID'].'">'.mb_htmlentities($aHuis['huisNaam']).'</a></td>
				<td>'.$aHuis['adres'].'</td>
				<td>'.$aHuis['telefoon'].'</td></tr>';
			$row++;
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
