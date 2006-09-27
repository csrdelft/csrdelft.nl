<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.csrmail.php
# -------------------------------------------------------------------
# Verzorgt het opvragen van pubciemailgegevens
# -------------------------------------------------------------------
# Historie:
# 01-10-2005 Jieter
# . gemaakt
#


class Csrmail {
	
	var $_db;
	var $_lid;
	
	function Csrmail(&$lid, &$db){
		# databaseconnectie openen
		$this->_lid =& $lid;
		$this->_db =& $db;
	}
	function magToevoegen(){ return $this->_lid->hasPermission('P_MAIL_POST'); }
	function magBeheren(){ return $this->_lid->hasPermission('P_MAIL_COMPOSE'); }
	function magVerzenden(){ return $this->_lid->hasPermission('P_MAIL_SEND'); }
	function getNaam($uid){ return $this->_lid->getCivitasName($uid); }
	
	function addBericht( $titel, $categorie, $bericht){
		$titel=ucfirst($this->_db->escape(trim($titel)));
		$volgorde=0;
		if(strtolower(trim($titel))=='agenda'){ $volgorde=-1000; }
		if(preg_match('/kamer/i', $titel)){ $volgorde=99; }
		if(preg_match('/ampel/i', $titel)){ $volgorde=999; }
		if(!$this->_isValideCategorie($categorie)){ $categorie='overig'; }
		$bericht=ucfirst($this->_db->escape(trim($bericht)));
		$uid=$this->_lid->getUid();
		$datumTijd=getDateTime();
		$sBerichtQuery="
			INSERT INTO
				pubciemailcache
			( 
				uid, titel, cat, bericht, datumTijd, volgorde
			)VALUES(
				'".$uid."', '".$titel."', '".$categorie."', '".$bericht."', '".$datumTijd."', ".$volgorde."
			);";
		
		return $this->_db->query($sBerichtQuery);
	}
	function bewerkBericht($iBerichtID, $titel, $categorie, $bericht){
		$iBerichtID=(int)$iBerichtID;
		$titel=ucfirst($this->_db->escape(trim($titel)));
		if(!$this->_isValideCategorie($categorie)){ $categorie='overig'; }
		$bericht=ucfirst($this->_db->escape(trim($bericht)));
		$sVoorwaarde='1';
		if(!$this->magBeheren()){
			//enkel de eigen berichten tonen.
			$uid=$this->_lid->getUid();
			$sVoorwaarde="uid='".$uid."'";
		}
		$datumTijd=getDateTime();
		$sBerichtQuery="
			UPDATE
				pubciemailcache
			SET
				titel='".$titel."', 
				cat='".$categorie."', 
				bericht='".$bericht."', 
				datumTijd='".$datumTijd."'
			WHERE
				".$sVoorwaarde."
			AND
				ID=".$iBerichtID."
			LIMIT 1;"; 
		return $this->_db->query($sBerichtQuery);
		
	}
	function valideerBerichtInvoer(&$sError){
		$bValid=true;
		$sError='';
		if(isset($_POST['titel']) AND isset($_POST['categorie']) AND isset($_POST['bericht'])){
			if(strlen(trim($_POST['titel'])) < 2 ){
				$bValid=false;
				$sError.='Het veld <strong>titel</strong> moet minstens 2 tekens bevatten.<br />';
			}
			if(strlen(trim($_POST['bericht'])) < 15 ){
				$bValid=false;
				$sError.='Het veld <strong>bericht</strong> moet minstens 15 tekens bevatten.<br />';
			}
		}else{
			$bValid=false;
			$sError.='Het formulier is niet compleet<br />';
		}
		return $bValid;
	}
	function _isValideCategorie($categorie){
		$aToegelatenCategorieen=array('bestuur', 'csr', 'overig', 'voorwoord');
		if(in_array($categorie, $aToegelatenCategorieen)){
			return true;
		}else{
			return false;
		}
	}
	function getBerichtenVoorGebruiker(){
		$sVoorwaarde='1';
		if(!$this->magBeheren()){
			//enkel de eigen berichten tonen.
			$uid=$this->_lid->getUid();
			$sVoorwaarde="uid='".$uid."'";
		}
		$sBerichtenQuery="
			SELECT
				ID, titel, cat, bericht, datumTijd, uid
			FROM
				pubciemailcache
			WHERE 
				".$sVoorwaarde."
			ORDER BY
				cat, volgorde, datumTijd;";
		$rBerichten=$this->_db->query($sBerichtenQuery);
		if($this->_db->numRows($rBerichten)==0){
			$aBerichten=false;
		}else{
			while($aData=$this->_db->next($rBerichten)){
				$aBerichten[]=$aData;
			}
		}
		return $aBerichten;
	}
	function getBerichtVoorGebruiker($iBerichtID){
		$iBerichtID=(int)$iBerichtID;
		$sVoorwaarde='1';
		if(!$this->magBeheren()){
			//enkel de eigen berichten ophalen voor niet-admins.
			$uid=$this->_lid->getUid();
			$sVoorwaarde="uid='".$uid."'";
		}
		$sBerichtenQuery="
			SELECT
				ID, titel, cat, bericht, datumTijd
			FROM
				pubciemailcache
			WHERE 
				".$sVoorwaarde."
			AND
				ID=".$iBerichtID."
			ORDER BY
				datumTijd;";
		$rBerichten=$this->_db->query($sBerichtenQuery);
		if($this->_db->numRows($rBerichten)==1){
			return $this->_db->next($rBerichten);
		}else{
			return false;
		}
	}
	function verwijderBerichtVoorGebruiker($iBerichtID){
		$iBerichtID=(int)$iBerichtID;
		$sVoorwaarde='1';
		if(!$this->magBeheren()){
			//enkel de eigen berichten tonen.
			$uid=$this->_lid->getUid();
			$sVoorwaarde="uid='".$uid."'";
		}
		$sBerichtVerwijderen="
			DELETE FROM
				pubciemailcache
			WHERE
				".$sVoorwaarde."
			AND
				ID='".$iBerichtID."'
			LIMIT 1;";
		$this->_db->query($sBerichtVerwijderen);
		return mysql_affected_rows()==1;
	}
	#############################################################
	###	functies voor compose gedeelte, voor de pubcie
	#############################################################
	function getBerichten($iMailID=0){
		$sBerichtenQuery="
			SELECT
				ID, titel, cat, bericht, datumTijd, uid, volgorde
			FROM
				pubciemailcache
			WHERE 
				1
			ORDER BY
				cat, volgorde, datumTijd;";
		$rBerichten=$this->_db->query($sBerichtenQuery);
		if($this->_db->numRows($rBerichten)==0){
			$aBerichten=false;
		}else{
			while($aData=$this->_db->next($rBerichten)){
				$aBerichten[]=$aData;
			}
		}
		return $aBerichten;
	}
	/*
	*	functie rost alles vanuit de tabel pubciemailcache naar de tabel 
	* pubciemail en pubciemailbericht.
	*/
	function moveFromCache(){
		$aBerichten=$this->getBerichten();
		$iPubciemailID=$this->createPubciemail();
		if(is_integer($iPubciemailID)){
			//kopieren dan maar
			foreach($aBerichten as $aBericht){
				$sMoveQuery="
					INSERT INTO
						pubciemailbericht
					(
						pubciemailID, titel, cat, bericht, volgorde
					)VALUES(
						".$iPubciemailID.", 
						'".$aBericht['titel']."', 
						'".$aBericht['cat']."', 
						'".$aBericht['bericht']."', 
						'".$aBericht['volgorde']."'
					);";
				$this->_db->query($sMoveQuery);
			}//einde foreach $aBerichten
			//cache leeggooien:
			$this->clearCache();
			return $iPubciemailID;
		}else{
			return false;
		}
	}
	function clearCache(){
		$sClearCache="
			TRUNCATE TABLE
				pubciemailcache;";
		return $this->_db->query($sClearCache);
	}
	function createPubciemail(){
		$uid=$this->_lid->getUid();
		$datumTijd=getDateTime();
		$sCreatePubciemailQuery="
			INSERT INTO
				pubciemail
			( 
				verzendMoment, verzender 
			) VALUES (
				'".$datumTijd."', '".$uid."'
			);";
		if($this->_db->query($sCreatePubciemailQuery)){
			return $this->_db->insert_id();
		}else{
			return false;
		}
	}
	
	
}//einde classe
?>
