<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.ledenlijstcontent.php
# -------------------------------------------------------------------
require_once 'groepen/groep.class.php';

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
			echo '<h1>Ongeldig uid</h1>';
		}else{
			$lid=LidCache::getLid($uid);
			echo '<h2><a class="forumGrootlink" href="/actueel/eetplan/">Eetplan</a> &raquo; voor '.$lid->getNaamLink('full', 'plain').'</h2>
				Profiel van '.$lid->getNaamLink('civitas','link').'<br /><br />';
			echo '<table class="eetplantabel">
				<tr><th style="width: 150px">Avond</th><th style="width: 200px">Huis</th></tr>';
			$row=0;
			foreach($aEetplan as $aEetplanData){
				$huis=new Groep($aEetplanData['groepid']);
				echo '
					<tr class="kleur'.($row%2).'">
						<td >'.$this->_eetplan->getDatum($aEetplanData['avond']).'</td>
						<td><a href="/actueel/eetplan/huis/'.$aEetplanData['huisID'].'"><strong>'.mb_htmlentities($aEetplanData['huisnaam']).'</strong></a><br />';
				if($huis instanceof Groep AND $huis->getId()!=0){
					echo 'Huispagina van '.$huis->getLink();
				}
				echo '</td></tr>';
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
			try{
				$huis=new Groep($aEetplan[0]['groepid']);
			}catch(Exception $e){
				$huis=new Groep(0); //hmm, dirty 
			}
			$sUitvoer='<table class="eetplantabel">
				<tr>
				<th style="width: 150px">Avond</th>
				<th style="width: 200px">&Uuml;bersjaarsch </th>
				<th>Mobiel</th>
				<th>E-mail</th>
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

				$lid=LidCache::getLid($aEetplanData['pheut']);
				$sUitvoer.='</td>
					<td>'.$lid->getNaamLink('civitas','link').'<br /></td>
					<td>'.mb_htmlentities($aEetplanData['mobiel']).'</td>
					<td>'.mb_htmlentities($aEetplanData['email']).'</td>
					<td>'.mb_htmlentities($aEetplanData['eetwens']).'</td>
					</tr>';

			}
			$sUitvoer.='</table>';
			echo '<h2><a class="forumGrootlink"href="/actueel/eetplan/">Eetplan</a> &raquo; voor '.mb_htmlentities($aEetplanData['huisnaam']).'</h2>
				'.mb_htmlentities($aEetplanData['huisadres']).' <br />';
			if($huis instanceof Groep AND $huis->getId()!=0){
				echo 'Huispagina: '.$huis->getLink().'<br /><br />';
			}
			echo $sUitvoer;
		}
	}
	function viewEetplan($aEetplan){
		$aHuizenArray=$this->_eetplan->getHuizen();
		//weergeven
		echo '
			<h1>Eetplan</h1>
			<div class="blokje"><h2>LET OP: </h2>
				Van eerstejaers die niet komen opdagen op het eetplan wordt verwacht dat zij minstens &eacute;&eacute;n keer komen koken op het huis waarbij zij gefaeld hebben.
			</div>
			<table class="eetplantabel">
			<tr><th style="width: 200px;">&Uuml;bersjaarsch/Avond</td>';
		//kopjes voor tabel
		for($iTeller=5;$iTeller<=8;$iTeller++){
			echo '<th class="huis">'.$this->_eetplan->getDatum($iTeller).'</th>';
		}
		echo '</tr>';
		$row=0;
		foreach($aEetplan as $aEetplanVoorPheut){


			echo '<tr class="kleur'.($row%2).'"><td><a href="/actueel/eetplan/sjaars/'.$aEetplanVoorPheut[0]['uid'].'">'.$aEetplanVoorPheut[0]['naam'].'</a></td>';
			for($iTeller=5;$iTeller<=8;$iTeller++){
				$huisnaam=$aHuizenArray[$aEetplanVoorPheut[$iTeller]-1]['huisNaam'];
				$huisnaam=str_replace(array('Huize ', 'De '), '', $huisnaam);
				$huisnaam=substr($huisnaam, 0, 15);

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
			try{
				$huis=new Groep($aHuis['groepid']);
			}catch(Exception $e){
				$huis=new Groep(0);
			}
			
			echo '<tr class="kleur'.($row%2).'">';
			echo '<td><a href="/actueel/eetplan/huis/'.$aHuis['huisID'].'">'.mb_htmlentities($aHuis['huisNaam']).'</a></td><td>';
			if($huis instanceof Groep AND $huis->getId()!=0){
				echo $huis->getLink();
			}
			echo '</td><td>'.$aHuis['telefoon'].'</td></tr>';
			$row++;
		}
		echo '</table>';
	}
	function view() {
		//kijken of er een pheut of een huis gevraagd wordt, of een overzicht.
		if(isset($_GET['pheutID'])){
			//eetplanavonden voor een pheut tonen
			$iPheutID=$_GET['pheutID'];
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
