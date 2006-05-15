<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.nieuwscontent.php
# -------------------------------------------------------------------
#
# Beeldt de berichten af die in een Nieuws-object zitten.
#
# -------------------------------------------------------------------
# Historie:
# 29-12-2004 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('bbcode/include.bbcode.php');
require_once ('class.nieuws.php');

class NieuwsContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_nieuws;

	# afbreken van de tekst van een berichtje bij de eerste spatie
	# voor het $chop-de karakter, 0 = niet gebruiken
	var $_chop = 400;
	var $_sError='';
	
	var $_berichtID;
	var $_actie='overzicht';

	### public ###

	function NieuwsContent (&$nieuws) {
		$this->_nieuws =& $nieuws;
	}

	function setChop($chars) { $this->_chop = (int)$chars; }
	function getNieuwBerichtLink(){
		if($this->_nieuws->isNieuwsMod()){
			echo '<hr /><a href="/nieuws/toevoegen">Nieuw nieuwsbericht toevoegen</a>';
		}
	}
	function getBerichtModControls($iBerichtID){
		if($this->_nieuws->isNieuwsMod()){
			echo '[ <a href="/nieuws/verwijderen/'.$iBerichtID.'" onclick="return confirm(\'Weet u zeker dat u dit nieuwsbericht wilt verwijderen?\')">verwijderen</a> | <a href="/nieuws/bewerken/'.$iBerichtID.'">bewerken</a> ]';
		}
	}
	function bewerkFormulier(){
		if($_SERVER['REQUEST_METHOD']!='post'){
			//gegevens direct ophaelen uit database
			$aBericht=$this->_nieuws->getMessage($this->_berichtID);
			$titel=$aBericht['titel'];
			$tekst=bbedit($aBericht['tekst'], $aBericht['bbcode_uid']);
			$prive=$verborgen='';
			if($aBericht['prive']==1){ $prive='checked="checked"'; }
			if($aBericht['verborgen']==1){ $verborgen='checked="checked"'; }
		}else{
			//wel een bericht om te bewerken, maar de varabelen uit _POST halen omdat het nog niet 
			//aan de eisen van $this->valideerFormulier() voldeed
			$titel=htmlspecialchars($_POST['titel']);
			$tekst=htmlspecialchars($_POST['tekst']);
			$prive=$verborgen='';
			if(isset($_POST['prive'])){ $prive='checked="checked"'; }
			if(isset($_POST['verborgen'])){ $verborgen='checked="checked"'; }
		}
		echo '<form action="/nieuws/bewerken/'.$this->_berichtID.'" method="post"><div class="pubciemail-form">';
		if($this->_sError!=''){ echo '<div class="foutmelding">'.$this->_sError.'</div>'; }
		echo '<strong>Titel</strong><br />
		<input type="text" name="titel" class="tekst" value="'.$titel.'" style="width: 100%;" /><br />
		<strong>Bericht</strong>&nbsp;&nbsp;';
		// link om het tekst-vak groter te maken.
		echo '<a href="#" onclick="vergrootTextarea(\'nieuwsBericht\', 10)" name="Vergroot het invoerveld">invoerveld vergroten</a><br />';
		echo '<textarea id="nieuwsBericht" name="tekst" cols="80" rows="10" style="width: 100%" class="tekst">'.$tekst.'</textarea><br />';
		echo '<input id="prive" type="checkbox" name="prive" '.$prive.' /><label for="prive">Dit bericht alleen weergeven bij leden</label>&nbsp;';
		echo '<input id="verborgen" type="checkbox" name="verborgen" '.$verborgen.' /><label for="verborgen">Dit bericht verbergen voor leden</label><br />';
		echo '<input type="submit" name="submit" value="opslaan" />&nbsp;<a href="/nieuws">Annuleren, terug naar nieuws</a></div>';
	}
	function nieuwFormulier(){
		$titel=$tekst=$prive=$verborgen='';
		if(isset($_POST['titel'])){ $titel=htmlspecialchars($_POST['titel']); }
		if(isset($_POST['tekst'])){ $tekst=htmlspecialchars($_POST['tekst']); }
		if(isset($_POST['prive'])){ $prive='checked="checked"'; }
		if(isset($_POST['verborgen'])){ $verborgen='checked="checked"'; }
		echo '<form action="/nieuws/toevoegen" method="post"><div class="pubciemail-form">';
		if($this->_sError!=''){ echo '<div class="foutmelding">'.$this->_sError.'</div>'; }
		echo '<strong>Titel</strong><br />
		<input type="text" name="titel" class="tekst" value="'.$titel.'" style="width: 100%;" /><br />
		<strong>Bericht</strong>&nbsp;&nbsp;';
		// link om het tekst-vak groter te maken.
		echo '<a href="#" onclick="vergrootTextarea(\'nieuwsBericht\', 10)" name="Vergroot het invoerveld">invoerveld vergroten</a><br />';
		echo '<textarea id="nieuwsBericht" name="tekst" cols="80" rows="10" style="width: 100%" class="tekst">'.$tekst.'</textarea><br />';
		echo '<input id="prive" type="checkbox" name="prive" '.$prive.' /><label for="prive">Dit bericht alleen weergeven voor leden</label>&nbsp;';
		echo '<input id="verborgen" type="checkbox" name="verborgen" '.$verborgen.' /><label for="verborgen">Dit bericht verbergen</label><br />';
		echo '<input type="submit" name="submit" value="opslaan"  />&nbsp;<a href="/nieuws">Annuleren, terug naar nieuws</a></div>';
	}
	function valideerFormulier(){
		$bNoError=true;
		if(!(isset($_POST['titel']) AND isset($_POST['tekst']))){
			$bNoError=false;
			$this->_sError.='Formulier is niet compleet<br />';
		}else{
			if(strlen($_POST['titel'])<2){
				$bNoError=false;
				$this->_sError.='Het veld <strong>titel</strong> moet minstens 2 tekens bevatten.<br />';
			}
			if(strlen($_POST['tekst'])<5){
				$bNoError=false;
				$this->_sError.='Het veld <strong>tekst</strong> moet minstens 5 tekens bevatten.<br />';
			}
		}
		return $bNoError;
	}
	function getOverzicht(){
		$aBerichten=$this->_nieuws->getMessages();
		if($aBerichten===false OR !is_array($aBerichten)) {	
			echo 'Zoals het is, zoals het was, o Civitas!<br />(Geen nieuws gevonden dus....)';
		}else{
			foreach ($aBerichten as $aBericht) {
				if(kapStringNetjesAf($aBericht['tekst'], $this->_chop)){
					//afgekapt
					$sBericht=bbview($aBericht['tekst'], $aBericht['bbcode_uid']);
					$sBericht.='... <a href="nieuws/'.$aBericht['id'].'">meer</a>';
				}else{
					$sBericht=bbview($aBericht['tekst'], $aBericht['bbcode_uid']);
				}
				echo '<span class="kopje3">';
				//verborgen berichten aangeven, enkel bij mensen met P_NEWS_MOD
				if($aBericht['verborgen']=='1'){ echo '<em>[verborgen] </em>';	}
				echo mb_htmlentities($aBericht['titel']).'</span>
						<i>('.date('d-m-Y H:i', $aBericht['datum']).')</i> ';
				//nieuwsbeheer functie dingen:
				$this->getBerichtModControls($aBericht['id']);
				echo '<br />'.$sBericht.'<br /><br />';
			}//einde foreach bericht
			$this->getNieuwBerichtLink();
		}
	}
	function getBericht(){
		$aBericht=$this->_nieuws->getMessage($this->_berichtID);
		if(is_array($aBericht)){
			//weergeven
			echo '<span class="kopje3">';
			//verborgen berichten aangeven, enkel bij mensen met P_NEWS_MOD
			if($aBericht['verborgen']=='1'){ echo '<em>[verborgen] </em>';	}
			echo mb_htmlentities($aBericht['titel']).'</span><i>('.date('d-m-Y H:i', $aBericht['datum']).')</i> ';
			//nieuwsbeheer functie dingen:
			$this->getBerichtModControls($aBericht['id']);
			echo '<br />'.bbview($aBericht['tekst'], $aBericht['bbcode_uid']).'<br />';
			$this->getNieuwBerichtLink();
		}else{
			echo 'Dit bericht bestaat niet, of is enkel zichtbaar voor ingelogde gebruikers.';
		}
	}
	function setBerichtID($iBerichtID){ $this->_berichtID=(int)$iBerichtID; }
	function setActie($sActie){	$this->_actie=$sActie; }
	
	function view(){
		echo '<h3>Nieuws</h3>';
		switch($this->_actie){
			case 'bewerken': $this->bewerkFormulier(); break;
			case 'bericht': $this->getBericht(); break;
			case 'toevoegen': $this->nieuwFormulier(); break;
			case 'overzicht': default: $this->getOverzicht(); break;
		}
	}
}

?>
