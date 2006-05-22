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
	function addBericht( $titel, $categorie, $bericht){
		$titel=$this->_db->escape(trim($titel));
		if(strtolower($titel)=='agenda'){ $volgorde=-1000; }else{ $volgorde=0; }
		if(!$this->isValideCategorie($categorie)){ $categorie='overig'; }
		$bericht=$this->_db->escape(trim($bericht));
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
		$titel=$this->_db->escape(trim($titel));
		if(!$this->isValideCategorie($categorie)){ $categorie='overig'; }
		$bericht=$this->_db->escape(trim($bericht));
		$uid=$this->_lid->getUid();
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
				uid='".$uid."'
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
			if($this->csrzonderpuntjes($_POST['bericht']) ){
		//	$bValid=false;
				$sError.='C.S.R. is met puntjes (bericht)!<br />';
			} 
			if($this->csrzonderpuntjes($_POST['titel']) ){
				$bValid=false;
				$sError.='C.S.R. is met puntjes (titel)!<br />';
			} 
		}else{
			$bValid=false;
			$sError.='Het formulier is niet compleet<br />';
		}
		return $bValid;
	}
	function isValideCategorie($categorie){
		$aToegelatenCategorieen=array('bestuur', 'csr', 'overig', 'voorwoord');
		if(in_array($categorie, $aToegelatenCategorieen)){
			return true;
		}else{
			return false;
		}
	}
	function csrzonderpuntjes($sBericht){
		$sBericht=strtolower(trim($sBericht));
		$aFoutCSR=array(' csr ', ' csr', 'csr ', ' csr.', 'cs.r', 'cs.r.', 'c.sr.', 'c.s.r ', 'c.sr', 'csrmail', 'csrmaaltijd', 'c s r', 'csrdelft');
		$bReturn=false;
		foreach($aFoutCSR as $sFout){
			if(is_integer(strpos($sBericht, $sFout))){
		  	$bReturn=true;
		  }
	  }
	  return $bReturn;
	}
	  
	
	function getBerichtenVoorGebruiker(){
		$uid=$this->_lid->getUid();
		$sBerichtenQuery="
			SELECT
				ID, titel, cat, bericht, datumTijd
			FROM
				pubciemailcache
			WHERE 
				uid='".$uid."'
			ORDER BY
				datumTijd;";
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
		$uid=$this->_lid->getUid();
		$sBerichtenQuery="
			SELECT
				ID, titel, cat, bericht, datumTijd
			FROM
				pubciemailcache
			WHERE 
				uid='".$uid."'
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
		return $aBerichten;
	}
	function verwijderBerichtVoorGebruiker($iBerichtID){
		$iBerichtID=(int)$iBerichtID;
		$uid=$this->_lid->getUid();
		$sBerichtVerwijderen="
			DELETE FROM
				pubciemailcache
			WHERE
				uid='".$uid."'
			AND
				ID='".$iBerichtID."'
			LIMIT 1;";
		$this->_db->query($sBerichtVerwijderen);
		if(mysql_affected_rows()==1){
			return true;
		}else{
			return false;
		}
	}
	#############################################################
	###	functies voor compose gedeelte, voor de pubcie
	#############################################################
	function getBerichten(){
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
			//fout
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
