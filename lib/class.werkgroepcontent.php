<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.werkgroepcontent.php
# -------------------------------------------------------------------


class WerkgroepContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_werkgroep;

	### public ###

	function WerkgroepContent (&$werkgroep) {
		$this->_werkgroep =& $werkgroep;
	}
	function getTitel(){
		return 'Werkgroepen';
	}
	function viewWaarbenik(){
		echo '<a href="/intern/">Intern</a> &raquo; Groepen &raquo; Werkgroepen';
	}
	function viewNewActieForm(){
		$naam=$beschrijving='';
		$limiet=10;
		if(isset($_POST['actieNaam'])){ $naam=trim(htmlspecialchars($_POST['actieNaam'])); }
		if(isset($_POST['beschrijving'])){ $beschrijving=trim(htmlspecialchars($_POST['beschrijving'])); }
		if(isset($_POST['limiet'])){ $limiet=(int)$_POST['limiet']; }
		$sError=$this->_werkgroep->getError();
		echo '<tr><td colspan="2">';
		echo '<h2 style="border-bottom: 1px dashed black; margin: 15px 0 0px 0;">Een activiteit toevoegen</h2></td></tr>';
		echo '<tr><td colspan="2">';

		echo '<form action="/groepen/werkgroepen.php" method="post"><div style="background-color: #F7F9FF; margin: 5px; padding: 5px; width: 710px;">';
		if(trim($sError)!=''){ echo '<div class="foutmelding">'.$sError.'</div>'; }
		echo '<strong>Naam:</strong> (van de activiteit)<br /><input type="text" value="'.$naam.'" name="actieNaam" class="tekst" style="width: 700px" /><br />';
		echo '<strong>Beschrijving:</strong><br /><textarea name="beschrijving" rows="8" class="tekst" style="width: 700px">'.$beschrijving.'</textarea><br />';
		echo '<strong>Limiet:</strong> (maximaal beschikbare plaatsen)<br /><input type="text" name="limiet" value="'.$limiet.'" class="tekst"  style="width: 700px" /><br />';
		echo '<input type="submit" value="verzenden" name="verzenden" />'; 

		echo '</div></form></td></tr>';
	}
	function view(){
		echo '<h1>Inschrijflijst</h1>';
		$aWerkgroepen=$this->_werkgroep->getWerkgroepen();
		
		echo '<table border="0" style="width: 100%">';
		echo '<tr><td><strong>Werkgroep</strong></td>';
		if ($this->_werkgroep->_lid->hasPermission('P_LEDEN_READ')) {
			echo '<td style="width: 200px;  border-left: 1px solid black; padding: 0 0 0 10px;"><strong>Opgaven</strong></td></tr>';
		}
		if(!is_array($aWerkgroepen)){
			echo '<tr><td colspan="2">Nog geen werkgroepen.</td></tr>';
		}else{
			foreach($aWerkgroepen as $aWerkgroep){
				$aSjaarsjes=$this->_werkgroep->getAanmeldingen($aWerkgroep['ID']);
				echo '<tr';
				if (is_array($aSjaarsjes) AND $aWerkgroep['limiet']-count($aSjaarsjes)<1){
					echo ' style="color: #aaaaaa;"';
				}				
				echo '><td colspan="2">';
				echo '<h2 style="border-bottom: 1px dashed black; margin: 15px 0 0px 0;">'.mb_htmlentities($aWerkgroep['actieNaam']).'</h2></td></tr>';
				echo '<tr';
				if (is_array($aSjaarsjes) AND $aWerkgroep['limiet']-count($aSjaarsjes)<1){
					echo ' style="color: #aaaaaa;"';
				}
				echo '><td>';
				echo nl2br(mb_htmlentities($aWerkgroep['beschrijving']));
				echo '</td>';

				if ($this->_werkgroep->_lid->hasPermission('P_LEDEN_READ')) {					
					echo '<td style="vertical-align: top; border-left: 1px solid black; padding: 0 0 0 10px;">';
					$bAlAangemeld=false;
					if(is_array($aSjaarsjes) AND count($aSjaarsjes)!=0){
						$iAantal=count($aSjaarsjes);
						foreach($aSjaarsjes as $aSjaars){
							echo $aSjaars['naamLink'].'<br />';
							//controleren of het huidige lid hier al is aangemeld. dan bAlAangemeld zetten.
							if($aSjaars['uid']==$this->_werkgroep->_lid->getUid()){ $bAlAangemeld=true; }
						}
						$limiet=$aWerkgroep['limiet']-$iAantal;
					}else{
						echo 'Nog geen aanmeldingen.<br />';
						$limiet=$aWerkgroep['limiet'];
					}
					if($limiet>=1){
						echo 'nog '.$limiet.' plaat'.($limiet==1 ? 's' : 'sen').' vrij.';
						if(!$bAlAangemeld){	
							echo '<br /><br /><a href="/groepen/werkgroepen.php?actieID='.$aWerkgroep['ID'].'&amp;aanmelden" 
								onclick="'."return confirm('Weet u zeker dat u wilt aanmelden voor deze werkgroep?')".'">aanmelden</a>';
						}
					}else{
						echo 'Deze activiteit is <strong>vol</strong>. U kunt zich niet meer aanmelden.';
					}
					echo '</td>';
				}
				echo '</tr>';
			}
		}//einde is_array($aWerkgroep)
		//voor Jaep formulier tonen...
		if($this->_werkgroep->_lid->getUid()=='0622'){ $this->viewNewActieForm(); }
		echo '</table>';	
	}
}
	
