<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.commissiecontent.php
# -------------------------------------------------------------------
#
# Beeldt informatie af over Commissies
#
# -------------------------------------------------------------------
# Historie:
# 29-12-2004 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('bbcode/include.bbcode.php');
require_once ('class.commissie.php');

class CommissieContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_commissie;
	var $_lid;

	### public ###

	function CommissieContent (&$commissie, &$lid) {
		$this->_commissie =& $commissie;
		$this->_lid =& $lid;
	}
	
	function viewCommissie($cie){
		if($this->_lid->hasPermission('P_LEDEN_READ')){
			//met commissieleden
			echo '<table border="0" width="100%">
				<tr><td ><center><span class="kopje2">'.$cie['titel'].'</span></center></td><td width="250px">&nbsp;</td></tr>
				<tr><td>'.bbview($cie['tekst'], $cie['bbcode_uid']);
			//eventueel link
			if ($cie['link'] != '') {
				echo 'CommissieWebstek: <a href="'.htmlspecialchars($cie['link']).'">'.mb_htmlentities($cie['link']).'</a>';	
			}
			echo '</td><td valign="top">';
			$aCieLeden=$this->_commissie->getCieLeden($cie['id']);
			if(is_array($aCieLeden)){
				echo '<table border="0"  class="hoktable" ><tr><td colspan="2"><strong>Commissieleden:</strong></td></tr>';
				foreach($aCieLeden as $aCieLid){
					echo '<tr><td width="150px">
						<a href="../leden/profiel/'.$aCieLid['uid'].'">'.mb_htmlentities($aCieLid['naam']).'</a>
						</td><td>'.mb_htmlentities($aCieLid['functie']);
					echo '</td>';
					if($this->_commissie->magBewerken()){
						echo '<td><a href="/informatie/commissie/'.$cie['id'].'/verwijder/lid/'.$aCieLid['uid'].'">X</a></td>';
					}
					
					echo '</tr>';
					
				}
				echo '</table>';
			}else{
				if($aCieLeden!==false){
					echo '<table border="0" cellpadding="5px" class="hoktable" ><tr><td>'.$aCieLeden.'</td></tr></table>';
				}
			}
			echo '</td></tr></table><a href="javascript: history.go(-1)">[ Terug ]</a>';
			//toevoegen van een lid aan commissie, voorlopig enkel nog door mods
			if($this->_cie->magBewerken()){
				echo '<hr /><h2>Deze commissie beheren:</h2>
					<br />
					<form action="?cie='.$cie['id'].'" method="post">';
				$tekstInvoer=true;
				if(isset($_POST['cieNamen'])){
					$aCieUids=namen2uid($_POST['cieNamen'], $this->_lid);
					if(count($aCieUids)!=0){
						echo '<table border="0">';
						echo '<tr><td><strong>Naam</strong></td><td><strong>Functie</strong></td></tr>';
						foreach($aCieUids as $aCieUid){
							if(isset($aCieUid['uid'])){
								//naam is gevonden en uniek, dus direct goed.
								echo '<tr>';
								echo '<td><input type="hidden" name="naam[]" value="'.$aCieUid['uid'].'" />'.$aCieUid['naam'].'</td>';
								echo '<td>'.$this->_getFunctieSelector().'</td></tr>';
							}else{
								//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
								if(count($aCieUid['naamOpties'])>0){
									echo '<tr><td><select name="naam[]" class="tekst">';
									foreach($aCieUid['naamOpties'] as $aNaamOptie){
										echo '<option value="'.$aNaamOptie['uid'].'">
											'.$aNaamOptie['voornaam'].' '.$aNaamOptie['achternaam'].'</option>';
									}
									echo '</select></td><td>'.$this->_getFunctieSelector($iCieLidTeller).'</td></tr>';
								}//dingen die niets opleveren wordt niets voor weergegeven.
							}
						}
						echo '</table>';
					}
					$tekstInvoer=false;
				}
				if($tekstInvoer){
					echo 'Geef hier namen of lidnummers op voor deze commissie, gescheiden door komma\'s<br />
						<textarea name="cieNamen" rows="4" cols="40" class="tekst"></textarea>';
				}
				echo '<input type="submit" value="Verzenden" /></form>';
			}
		}else{
			//zonder commissieleden
			echo '<table border="0" width="100%">
				<tr><td><center><span class="kopje2">'.$cie['titel'].'</span></center></td></tr>
				<tr><td>'.bbview($cie['tekst'], $cie['bbcode_uid']);
			//eventueel link
			if ($cie['link'] != '') {
				echo 'CommissieWebstek: <a href="'.htmlspecialchars($cie['link']).'">'.mb_htmlentities($cie['link']).'</a>';	
			}
			echo '</td></tr></table><a href="javascript: history.go(-1)">[ Terug ]</a>';
		}
		
	}
	function _getFunctieSelector(){
		$return='';
		$aFuncties=array('Q.Q.', 'Praeses', 'Fiscus', 'Redacteur', 'Computeur', 'Archivaris', 
			'Bibliothecaris', 'Statisticus', 'Fotocommissaris','', 'Koemissaris', 'Regisseur', 
			'Lichttechnicus', 'Geluidstechnicus');
		sort($aFuncties);
		$return.='<select name="functie[]" class="tekst">';
		foreach($aFuncties as $sFunctie){
			$return.='<option value="'.$sFunctie.'">'.$sFunctie.'</option>';
		}
		$return.='</select>';
		return $return;
	}
		
	function view() {
		$cie = $this->_commissie->getCommissie();
		echo $this->viewCommissie($cie);
	}
}

?>
