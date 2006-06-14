<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.csrmailcontent.php
# -------------------------------------------------------------------
# Verzorgt het opvragen van eetplangegevens
# -------------------------------------------------------------------
# Historie:
# 01-10-2005 Jieter
# . gemaakt
#

require_once ('class.mysql.php');

class Csrmailcontent {
	
	var $_csrmail;				//db object voor de csrmail
	
	var $_edit=0;					//bericht wat bewerkt moet worden.
	
	var $_sError=false;					//fouten in formulieren, bijvoorbeeld 'geef een titel mee'
	var $_userMessage=''; //dingen als 'het is gelukt' of 'bericht verwijderd'
	
	function Csrmailcontent(&$csrmail){
		$this->_csrmail=$csrmail;
		//kijken of er nog een bericht getoond moet worden uit de sessie
		if(isset($_SESSION['csrmail_error'])){
			$this->_userMessage=trim($_SESSION['csrmail_error']);
			unset($_SESSION['csrmail_error']);
		}
	}
	//functie die gebruikt wordt voor het bewerken van bestaande en het maken van nieuwe berichten.
	function _geefBerichtInvoerVeld($titel, $categorie, $bericht, $ID=0){
		echo '<form method="post" action="?ID='.$ID.'" ><div class="pubciemail-form">';
		if($this->_sError!==false){ echo '<div class="foutmelding">'.$this->_sError.'</div>'; }
		echo '<strong>Titel:</strong><br />';
		echo '<input type="text" name="titel" value="'.htmlspecialchars($titel).'" style="width: 100%;" class="tekst" />';
		echo '<br /><br /><strong>Categorie:</strong><br />';
		echo 'Selecteer hier een categorie. Uw invoer is enkel een voorstel.';
		echo '<em>Aankondigingen over kamers te huur komen in <strong>overig</strong> terecht! C.S.R. is bedoeld voor';
		echo 'activiteiten van C.S.R.-commissies en andere verenigingsactiviteiten.</em><br />';
		echo '<select name="categorie" class="tekst">';
		//mogelijke categorieën
		$aOpties=array(
			array('voorwoord', 'Voorwoord'), array('bestuur', 'Bestuur'), 
			array('csr', 'C.S.R.'), array('overig', 'Overig'));
		if(trim($categorie)=='')
			$categorie='overig';
		foreach($aOpties as $aOptie){
			if(!($aOptie[0]=='voorwoord' AND !$this->_csrmail->_lid->hasPermission('P_MAIL_COMPOSE'))){
				if($aOptie[0]==$categorie){
					echo '<option value="'.$aOptie[0].'" selected="selected">'.$aOptie[1].'</option>';
				}else{
					echo '<option value="'.$aOptie[0].'">'.$aOptie[1].'</option>';
				}
			}
		}
		echo '</select><br /><br /><strong>Bericht:</strong><br />';
		echo '<textarea name="bericht" cols="80" style="width: 100%;" rows="15" class="tekst">';
		echo htmlspecialchars($bericht);
		echo '</textarea>';
		echo '<input type="submit" name="verzendenMeer" value="opslaan" class="tekst" /></div></form>';
	}
	
	function _geefBerichtNieuw($sError=false){
		$titel=$categorie=$bericht='';
		if(isset($_POST['titel'])) $titel=trim($_POST['titel']);
		if(isset($_POST['categorie'])) $categorie=trim($_POST['categorie']);
		if(isset($_POST['bericht'])) $bericht=trim($_POST['bericht']);
		echo '<h3>Nieuw bericht invoeren</h3>
			Hier kunt u uw bericht(en) voor de pubCie-post achterlaten.<br />';
		$this->_geefBerichtInvoerVeld($titel, $categorie, $bericht);
	}	
	function _geefBerichtBewerken($sError){
		$iBerichtID=(int)$this->_edit;
		//bericht ophalen
		if($_SERVER['REQUEST_METHOD']=='POST'){
			//bewerken, maar wel de ingevoerde waarden gebruiken.
			$aBericht['titel']=$aBericht['bericht']='';
			$aBericht['cat']='csr';
			//kijken of er nog gegevens in de POST array staan.
			if(isset($_POST['titel'])) $aBericht['titel']=trim($_POST['titel']);
			if(isset($_POST['categorie'])) $aBericht['cat']=trim($_POST['categorie']);
			if(isset($_POST['bericht'])) $aBericht['bericht']=trim($_POST['bericht']);
		}else{
			//gegevens uit de database bewerken.
			$aBericht=$this->_csrmail->getBerichtVoorGebruiker($iBerichtID);
		}
		if(is_array($aBericht)){
			//bericht daadwerkelijk in het formulier rossen
			$this->_geefBerichtInvoerVeld(
				stripslashes($aBericht['titel']), $aBericht['cat'], stripslashes($aBericht['bericht']), $sError, $iBerichtID);
		}else{
			//bericht bestaat niet of is niet van gebruiker
			echo '<h3>Helaas</h3>U mag dit bericht niet bewerken, omdat het niet bestaat of niet van u is.';
		}
	}
	function _toonBerichten(){
		$aBerichten=$this->_csrmail->getBerichtenVoorGebruiker();
		echo '<h3>Overzicht van door u geplaatste berichten:</h3>';
		if(is_array($aBerichten)){
			echo '<dl>';
			foreach($aBerichten as $aBericht){
				echo '<dt><u>'.str_replace('csr', 'C.S.R.', $aBericht['cat']).'</u> <strong>'.$aBericht['titel'].'</strong> ';
				//bewerken en verwijderen linkjes.
				echo '[ <a href="bewerken/'.$aBericht['ID'].'">bewerken</a> | ';
				echo '<a href="verwijder/'.$aBericht['ID'].'">verwijderen</a> ]</dt>';
				echo '<dd>'.$this->_process($aBericht['bericht']).'</dd>';
			}
		}else{
			echo 'U heeft nog geen berichten geplaatst in deze pubcie-mail;';
		}
	}
	function _process($sString){
		$sString=stripslashes($sString);
		$sString=mb_htmlentities($sString);
		$sString=trim($sString);
		 $aUbbCodes=array(
      array("[b]", "<strong>"), array("[/b]", "</strong>"),
      array("[i]", "<em>"), array("[/i]", "</em>"),
      array("[u]", "<span class=\"onderlijn\">"), array("[/u]", "</span>"));
    foreach($aUbbCodes as $ubbCode){
    	$sString=str_replace($ubbCode[0], $ubbCode[1], $sString);
 		}
		//linkjes
		$sString=eregi_replace("\\[url=([^\\[]*)\]([^\\[]*)\\[/url\\]","<a href=\"\\1\" >\\2</a>", $sString);
		$sString=nl2br($sString);
		return $sString;
	}
	
	function addUserMessage($sMessage, $refresh=true){ 
		if($refresh){
			$_SESSION['csrmail_error']=trim($sMessage);
			header('location: http://csrdelft.nl/leden/csrmail/');
		}else{	
			$this->_userMessage=trim($sMessage);
		}
	}
	function addEditForm($iBerichtID, $sError=false){
		$iBerichtID=(int)$iBerichtID;
		$this->_edit=$iBerichtID;
		$this->_sError=$sError;
	}
	function addNewForm($sError=false){
		$this->_sError=$sError;
	}
	
	function view(){
		echo '<h2>PubCie-post</h2>';
		if($this->_csrmail->magBeheren()){
			echo '<a href="voorbeeld.php" target="_blank" >Voorbeeld</a>';
		}
		//eventuele melding printen.
		if($this->_userMessage!=''){ echo '<div class="pubciemail-form">'.trim($this->_userMessage).'</div>'; }
		if($this->_edit!==0){
			//invoerformulier tonen, alsmede een overzicht van berichten
			$this->_geefBerichtBewerken($this->_sError);
		}elseif(is_string($this->_sError)){
			//fout tijdens het invoeren van een nieuw bericht, het formulier is er nog eens, 
			//maar dan met de al ingevoerde waarden
			$this->_geefBerichtNieuw($this->_sError);
		}else{
			//overzicht van berichten plus een formulier voor een nieuw bericht.
			$this->_toonBerichten();
			$this->_geefBerichtNieuw();
		}
		
	}
}//einde classe
?>
