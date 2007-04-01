<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.inschrijvingcontent.php
# -------------------------------------------------------------------


class InschrijvingContent {
	
	# private
	var $_inschrijving; 
	
	#public
	function InschrijvingContent(&$inschrijving){
		$this->_inschrijving =& $inschrijving;
	}
	
	function viewNewInschrijvingForm(){
		$naam=$beschrijving='';
		$limiet=30;
		if(isset($_POST['inschrijvingNaam'])){ $naam=trim(htmlspecialchars($_POST['inschrijvingNaam'])); }
		if(isset($_POST['beschrijving'])){ $beschrijving=trim(htmlspecialchars($_POST['beschrijving'])); }
		if(isset($_POST['limiet'])){ $limiet=(int)$_POST['limiet']; }
		if(isset($_POST['partnereis'])){ $partnereis=(int)$_POST['partnereis']; }
		$sError=$this->_inschrijving->getError();
		echo '<tr><td colspan="2">';
		echo '<h2 style="border-bottom: 1px dashed black; margin: 15px 0 0px 0;">Een activiteit toevoegen</h2></td></tr>';
		echo '<tr><td colspan="2">';

		echo '<form action="/intern/inschrijving/inschrijving.php" method="post"><div style="background-color: #F7F9FF; margin: 5px; padding: 5px; width: 710px;">';
		if(trim($sError)!=''){ echo '<div class="foutmelding">'.$sError.'</div>'; }
		echo '<strong>Naam:</strong> (van de activiteit)<br /><input type="text" value="'.$naam.'" name="inschrijvingNaam" class="tekst" style="width: 700px" /><br />';
		echo '<strong>Datum:</strong>(waarop de activiteit plaatsvind, formaat jjjj-mm-dd)<br /><input type="text" value="'.$datum.'" name="datum" class="tekst" style="width: 700px" /><br />';		
		echo '<strong>Beschrijving:</strong><br /><textarea name="beschrijving" rows="8" class="tekst" style="width: 700px">'.$beschrijving.'</textarea><br />';
		echo '<strong>Limiet:</strong> (maximaal beschikbare plaatsen)<br /><input type="text" name="limiet" value="'.$limiet.'" class="tekst"  style="width: 700px" /><br />';
		echo '<strong>Inschrijven alleen met partner?</strong><input type="checkbox" value="'.$partnereis.'" class="tekst"  style="width: 30px" /><br />';		
		echo '<input type="submit" value="verzenden" name="verzenden" />'; 

		echo '</div></form></td></tr>';
	}
	function viewAanAfmelden($aInschrijving){
		$aDeelnemers=$this->_inschrijving->getAanmeldingen($aInschrijving['ID']);
		$bAlAangemeld=false;
		if(is_array($aDeelnemers) AND count($aDeelnemers)!=0){
			$iAantal=count($aDeelnemers);
			foreach($aDeelnemers as $aDeelnemer){
				//Weergeven van alle deelnemers hier liever niet.
				//echo '<a href="/intern/profiel/'.$aDeelnemer['uid'].'">'.mb_htmlentities($aDeelnemer['naam']).'</a><br />';				
				//controleren of het huidige lid hier al is aangemeld. dan bAlAangemeld zetten.
				if($aDeelnemer['uid']==$this->_inschrijving->_lid->getUid()){ 
					$bAlAangemeld=true; 
				}
			}
			$limiet=$aInschrijving['limiet']-$iAantal;
		}else{
			//echo 'Nog geen aanmeldingen.<br />';
			$limiet=$aInschrijving['limiet'];
			}
		if($limiet>=1){
			echo 'Nog '.$limiet.' plaat'.($limiet==1 ? 's' : 'sen').' vrij.';
			if(!$bAlAangemeld){ #misschien controle op lidzijn, waarschijnlijk niet nodig
				echo '<br />
					<form action="/intern/inschrijving/inschrijving.php" method="get">
					<hidden name=inschrijvingID value='.$aInschrijving['ID'].'>
					<hidden name=aanmelden value="">';
				//Partner vereist voor deze activiteit?								
				if($aInschrijving['partnereis'] == 'ja'){
					echo 'Partner:<br /><input type="text" name="partner" /><br />
							<input type="checkbox" name="intern" class="text" /> C.S.R.-partner <br />
							Eetwens partner:<br /><input type="text" name="eetwens_partner" /><br />
						  ';	
				}
				echo '<input type=submit onclick="'."return confirm('Weet u zeker dat u zich wilt inschrijven voor deze activiteit?')".'" value=inschrijven />
					  </form>';
			}
		}else{
			echo 'Deze activiteit is <strong>vol</strong>. U kunt zich niet meer inschrijven.';
		}
		if($bAlAangemeld){
			echo '<br /><br /><a href="/intern/inschrijving/inschrijving.php?inschrijvingID='.$aInschrijving['ID'].'&amp;afmelden" 
				onclick="'."return confirm('Weet u zeker dat u zich wilt afmelden voor deze activiteit?')".'">afmelden</a>';
		}				
		echo '</td></tr>';
	}
	
	function view(){
		echo '<h1>Inschrijven</h1>';		
		$aInschrijvingen=$this->_inschrijving->getInschrijvingen();
		echo '<table border="0" style="width: 100%">';
		echo '<tr><td><h3>Activiteit</h3></td><td style="width: 200px;  border-left: 1px solid black; padding: 0 0 0 10px;"><h3>Inschrijven</h3></td></tr>';
		if(!is_array($aInschrijvingen)){
			echo '<tr><td colspan="2">Er zijn nog geen activiteiten om u voor in te schrijven.</td></tr>';
		}else{
			foreach($aInschrijvingen as $aInschrijving){
				echo '<tr><td colspan="2">';
				echo '<h2 style="border-bottom: 1px dashed black; margin: 15px 0 0px 0;">'.mb_htmlentities($aInschrijving['inschrijvingNaam']).'</h2></td></tr>';
				echo '<tr><td><strong>Verantwoordelijke: ';
				echo '<a href="/intern/profiel/'.$aInschrijving['uid'].'" style="font-weight: bold;">';
				echo mb_htmlentities($aInschrijving['naam']);
				echo '</a></strong><br /><br />';
				echo nl2br(mb_htmlentities($aInschrijving['beschrijving']));
				echo '</td><td style="vertical-align: top; border-left: 1px solid black; padding: 0 0 0 10px;">';
				$this->viewAanAfmelden($aInschrijving);				
			}
		}//einde is_array($aInschrijvingen)
		//voor moderatoren e.d. een activiteitenformulier tonen...
		if($this->_inschrijving->magOrganiseren())
		{ $this->viewNewInschrijvingForm(); }
		echo '</table>';	
	}
}
?>