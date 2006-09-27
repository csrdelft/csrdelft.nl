<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.sjaarsactiecontent.php
# -------------------------------------------------------------------
#
# -------------------------------------------------------------------



class SjaarsactieContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_sjaaractie;

	### public ###

	function SjaarsactieContent (&$sjaarsactie) {
		$this->_sjaarsactie =& $sjaarsactie;
	}
	
	function viewNewActieForm(){
		$naam=$beschrijving='';
		$limiet=10;
		if(isset($_POST['actieNaam'])){ $naam=trim(htmlspecialchars($_POST['actieNaam'])); }
		if(isset($_POST['beschrijving'])){ $beschrijving=trim(htmlspecialchars($_POST['beschrijving'])); }
		if(isset($_POST['limiet'])){ $limiet=(int)$_POST['limiet']; }
		$sError=$this->_sjaarsactie->getError();
		echo '<tr><td colspan="2">';
		echo '<h2 style="border-bottom: 1px dashed black; margin: 15px 0 0px 0;">Een sjaarsactie aanmelden</h2></td></tr>';
		echo '<tr><td colspan="2">';

		echo '<form action="/leden/sjaarsacties.php" method="post"><div style="background-color: #F7F9FF; margin: 5px; padding: 5px; width: 710px;">';
		if(trim($sError)!=''){ echo '<div class="foutmelding">'.$sError.'</div>'; }
		echo '<strong>Naam:</strong> (van de sjaarsactie)<br /><input type="text" value="'.$naam.'" name="actieNaam" class="tekst" style="width: 700px" /><br />';
		echo '<strong>Beschrijving:</strong><br /><textarea name="beschrijving" rows="8" class="tekst" style="width: 700px">'.$beschrijving.'</textarea><br />';
		echo '<strong>Limiet:</strong> (maximaal beschikbare plaatsen)<br /><input type="text" name="limiet" value="'.$limiet.'" class="tekst"  style="width: 700px" /><br />';
		echo '<input type="submit" value="verzenden" name="verzenden" />'; 

		echo '</div></form></td></tr>';
	}
	function view(){
		echo '<h1>Sjaarsacties</h1>';
		$aSjaarsacties=$this->_sjaarsactie->getSjaarsActies();
		
		echo '<table border="0" style="width: 100%">';
		echo '<tr><td><strong>Actie</strong></td><td style="width: 200px;  border-left: 1px solid black; padding: 0 0 0 10px;"><strong>Opgaven</strong></td></tr>';
		if(!is_array($aSjaarsacties)){
			echo '<tr><td colspan="2">Nog geen sjaarsacties aangemeld</td></tr>';
		}else{
			foreach($aSjaarsacties as $aSjaarsactie){
				echo '<tr><td colspan="2">';
				echo '<h2 style="border-bottom: 1px dashed black; margin: 15px 0 0px 0;">'.mb_htmlentities($aSjaarsactie['actieNaam']).'</h2></td></tr>';
				echo '<tr><td><strong>Ouderejaars: ';
				echo '<a href="/leden/profiel/'.$aSjaarsactie['uid'].'" style="font-weight: bold;">';
				echo mb_htmlentities($aSjaarsactie['naam']);
				echo '</a></strong><br /><br />';
				echo nl2br(mb_htmlentities($aSjaarsactie['beschrijving']));
				echo '</td><td style="vertical-align: top; border-left: 1px solid black; padding: 0 0 0 10px;">';
				$aSjaarsjes=$this->_sjaarsactie->getAanmeldingen($aSjaarsactie['ID']);
				$bAlAangemeld=false;
				if(is_array($aSjaarsjes) AND count($aSjaarsjes)!=0){
					$iAantal=count($aSjaarsjes);
					foreach($aSjaarsjes as $aSjaars){
						echo '<a href="/leden/profiel/'.$aSjaars['uid'].'">'.mb_htmlentities($aSjaars['naam']).'</a><br />';
						//controleren of de huidige sjaard hier al is aangemeld. dan bAlAangemeld zetten.
						if($aSjaars['uid']==$this->_sjaarsactie->_lid->getUid()){ $bAlAangemeld=true; }
					}
					$limiet=$aSjaarsactie['limiet']-$iAantal;
				}else{
					echo 'Nog geen aanmeldingen.<br />';
					$limiet=$aSjaarsactie['limiet'];
				}
				if($limiet>=1){
					echo 'nog '.$limiet.' plaat'.($limiet==1 ? 's' : 'sen').' vrij.';
					if(!$bAlAangemeld AND $this->_sjaarsactie->isSjaars()){	
						echo '<br /><br /><a href="/leden/sjaarsacties.php?actieID='.$aSjaarsactie['ID'].'&amp;aanmelden" 
							onclick="'."return confirm('Weet u zeker dat u wilt aanmelden voor deze sjaarsactie?')".'">aanmelden</a>';
					}
				}else{
					echo 'Deze sjaaractie is <span style="color: red;">vol</span>. U kunt zich niet meer aanmelden.';
				}
				echo '</td></tr>';
			}
		}//einde is_array($aSjaaracties)
		//voor niet-sjaars het formulier tonen...
		if(!$this->_sjaarsactie->isSjaars()){ $this->viewNewActieForm(); }
		echo '</table>';	
	}
}
	
