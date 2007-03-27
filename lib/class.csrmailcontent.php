<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.csrmailcontent.php
# -------------------------------------------------------------------
#

require_once ('class.mysql.php');

class Csrmailcontent {
	
	var $_csrmail;				//db object voor de csrmail
	
	var $_edit=0;					//bericht wat bewerkt moet worden.
	
	var $_sError=false;		//fouten in formulieren, bijvoorbeeld 'geef een titel mee'
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
		echo '<form method="post" action="?ID='.$ID.'" ><div id="pubciemail_form">';
		if($this->_sError!==false){ echo '<div class="foutmelding">'.$this->_sError.'</div>'; }
		echo '<strong>Titel:</strong><br />';
		echo '<input type="text" name="titel" value="'.htmlspecialchars($titel).'" style="width: 100%;" class="tekst" />';
		echo '<br /><br /><strong>Categorie:</strong><br />';
		echo 'Selecteer hier een categorie. Uw invoer is enkel een voorstel. ';
		echo '<em>Aankondigingen over kamers te huur komen in <strong>overig</strong> terecht! C.S.R. is bedoeld voor ';
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
		echo '
			<h3>Nieuw bericht invoeren</h3>
			<p>Hier kunt u uw bericht(en) voor de C.S.R.-courant achterlaten.</p>';
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
		echo '<h3>Overzicht van berichten:</h3>';
		if(is_array($aBerichten)){
			echo '<dl>';
			foreach($aBerichten as $aBericht){
				echo '<dt><u>'.str_replace('csr', 'C.S.R.', $aBericht['cat']).'</u> ';
				if($this->_csrmail->magBeheren()){ 
					echo ' ('.$this->_csrmail->getNaam($aBericht['uid']).') ';
				}
				echo '<strong>'.$aBericht['titel'].'</strong> ';
				//bewerken en verwijderen linkjes.
				echo '[ <a href="bewerken/'.$aBericht['ID'].'">bewerken</a> | ';
				echo '<a href="verwijder/'.$aBericht['ID'].'" onclick="return confirm(\'Weet u zeker dat u dit bericht wilt verwijderen?\')" >verwijderen</a> ]</dt>';
				if(!$this->_csrmail->magBeheren()){
					echo '<dd>'.$this->_process($aBericht['bericht']).'</dd>';
				}
			}
			echo '</dl>';
		}else{
			echo 'U heeft nog geen berichten geplaatst in deze C.S.R.-courant;';
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
		$sString=eregi_replace("\\[img\]([^\\[]*)\\[/img\\]","<img src=\"\\1\" />", $sString);
		$sString=nl2br($sString);
		return $sString;
	}
	function _getBody($iMailID=0){
		$sTemplate=file_get_contents(LIB_PATH.'/templates/csrmail/'.CSRMAIL_TEMPLATE);
		$aBerichten=$this->_csrmail->getBerichten($iMailID);
		if(is_array($aBerichten)){
			//lege array's klussen voor als er geen data is voor de categorie
			$aInhoud['bestuur']=$aInhoud['csr']=$aInhoud['overig']='';
			$aKopjes['bestuur']=$aKopjes['csr']=$aKopjes['overig']=array('Geen berichten');
			//kopjes uit de berichten halen
			$aKopjes=$this->_getKopjes($aBerichten);
			
			foreach($aKopjes as $sCategorie => $aKopjesPerCat){
				foreach($aKopjesPerCat as $aKopje){
					$aInhoud[$sCategorie].='<li><a href="#'.$aKopje['ID'].'" style="text-decoration: none;">
						'.mb_htmlentities($aKopje['titel']).'</a></li>'."\r\n";
				}
			}
			reset($aBerichten);
			$sBerichten='';
			foreach($aBerichten as $aBericht){
				$sBerichten.='<h4><a name="'.$aBericht['ID'].'"></a>'.$this->_process($aBericht['titel']) .'</h4>'."\r\n";
				$sBerichten.='<p>'.$this->_process($aBericht['bericht']).'</p>'."\r\n";
			}   
			$sTemplate=str_replace('[inhoud-bestuur]', $aInhoud['bestuur'], $sTemplate);
			$sTemplate=str_replace('[inhoud-csr]', $aInhoud['csr'], $sTemplate);
			$sTemplate=str_replace('[inhoud-overig]', $aInhoud['overig'], $sTemplate);
			$sTemplate=str_replace('[berichten]', $sBerichten, $sTemplate);
		}else{
			$sTeplate='Geen berichten aanwezig;';
		}
		return $sTemplate;
	}
	function _getKopjes($aBerichten){
		foreach($aBerichten as $aBericht){
			if($aBericht['cat']!='voorwoord'){
				//ros alles in een array, met categorie als element.
				$aKopjes[$aBericht['cat']][]=array('titel'=> $aBericht['titel'], 'ID' => $aBericht['ID']);
			}
		}
		return $aKopjes;
	}
	function _getArchiefmails(){
		$aMails=$this->_csrmail->getArchiefmails();
		$sReturn='<h3>Archief</h3>';
		foreach($aMails as $aMail){
			$sReturn.='<a href="/intern/csrmail/archief/'.$aMail['ID'].'">'.$aMail['verzendMoment'].'</a><br />';
		}
		return $sReturn;
	}
	function _voorbeeldIframe(){
		echo '<br /><h3>Voorbeeld van de C.S.R.-courant</h3>
			<iframe src="/intern/csrmail/voorbeeld.php" style="width: 100%; height: 250px;"></iframe>';
	}
	function addUserMessage($sMessage, $refresh=true){ 
		if($refresh){
			$_SESSION['csrmail_error']=trim($sMessage);
			header('location: '.CSR_ROOT.'intern/csrmail/' );
		}else{	
			$this->_userMessage=trim($sMessage);
		}
	}
	function addEditForm($iBerichtID, $sError=false){
		$iBerichtID=(int)$iBerichtID;
		$this->_edit=$iBerichtID;
		$this->_sError=$sError;
	}
	function addNewForm($sError=false){ $this->_sError=$sError; }
	
	function getTitel(){ return 'C.S.R.-courant beheer'; }

	function view(){
		echo '<h2>C.S.R.-courant</h2>';
		if($this->_csrmail->magBeheren()){
			echo '<a href="/intern/csrmail/voorbeeld.php" class="knop">Voorbeeld</a> 
			<a href="/intern/csrmail/verzenden.php" onclick="return confirm(\'Weet u het zeker dat u de C.S.R.-courant wilt versturen?\')" class="knop">Verzenden</a> 
			<a href="/intern/csrmail/leegmaken" class="knop" onclick="return confirm(\'Weet u zeker dat u de cache wilt leeggooien?\')">Leegmaken</a>';
		}
		//eventuele melding printen.
		if($this->_userMessage!=''){ echo '<div class="waarschuwing">'.trim($this->_userMessage).'</div>'; }
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
			if($this->_csrmail->magBeheren()){
				$this->_voorbeeldIframe();
			}
		}
		
	}
}//einde classe
?>
