<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.nieuws.php
# -------------------------------------------------------------------
# Verzorgt het opvragen en opslaan van nieuwsberichten.
# Wordt o.a. door NieuwsContent gebruikt
# -------------------------------------------------------------------
# Historie:
# 29-12-2004 Hans van Kranenburg
# . gemaakt
#

require_once ('class.mysql.php');

class Nieuws {

	### private ###
	var $_lid;
	var $_db;
	
	var $_aantal=5;
	
	function Nieuws(&$db, &$lid){
		$this->_db=&$db;
		$this->_lid=&$lid;
	}
	### public ###

	#
	# ophaelen nieuwsberichten
	# $iBerichtID == 0 -> alles ophaelen
	# $iBerichtID != 0 -> alleen opgegeven nummer
	#
	function getMessages($iBerichtID=0) {
		$iBerichtID=(int)$iBerichtID;
		//where clausule klussen
		$sWhereClause='';
		if(!$this->_lid->isLoggedIn()){ $sWhereClause.="nieuws.prive!='1' AND "; }
		if(!$this->isNieuwsMod()){ $sWhereClause.="nieuws.verborgen!='1' AND "; }
		if($iBerichtID!=0){ $sWhereClause.="nieuws.id=".$iBerichtID." AND "; }
		
		$sNieuwsQuery="
			SELECT
				nieuws.id as id, 
				nieuws.datum as datum, 
				nieuws.titel as titel, 
				nieuws.tekst as tekst, 
				nieuws.bbcode_uid as bbcode_uid, 
				nieuws.uid as uid, 
					lid.voornaam as voornaam, lid.achternaam as achternaam, lid.tussenvoegsel as tussenvoegsel,
				nieuws.prive as prive, 
				nieuws.verborgen as verborgen,
				nieuws.plaatje as plaatje
			FROM
				nieuws
			INNER JOIN
				lid ON( nieuws.uid=lid.uid )
			WHERE
				".$sWhereClause."
				nieuws.verwijderd='0'
			ORDER BY
				nieuws.datum DESC
			LIMIT
				0, ".$this->_aantal.";";
		$rNieuwsBerichten=$this->_db->query($sNieuwsQuery);
		if($iBerichtID!=0){
			return $this->_db->next($rNieuwsBerichten);
		}else{
			return $this->_db->result2array($rNieuwsBerichten);
		}
	}
	function getMessage($iBerichtID){ return $this->getMessages($iBerichtID);	}

	//bericht toevoegen
	function addMessage($titel, $tekst, $bbcode_uid, $prive=false, $verborgen=false){
		$datum=time();
		$titel=$this->_db->escape($titel);
		$tekst=$tekst;
		if($prive){$prive=1; }else{ $prive=0; }
		if($verborgen){$verborgen=1; }else{ $verborgen=0; }
		$uid=$this->_lid->getUid();
		$sMessageQuery="
			INSERT INTO
				nieuws
			( 
				datum, titel, tekst, bbcode_uid, uid, prive, verborgen
			) VALUES (
				".$datum.", '".$titel."', '".$tekst."', '".$bbcode_uid."', '".$uid."', '".$prive."', '".$verborgen."'
			);";
		return $this->_db->query($sMessageQuery);
	}
	function deleteMessage($iBerichtID){
		$iBerichtID=(int)$iBerichtID;
		$sMessageQuery="
			UPDATE
				nieuws
			SET
				verwijderd='1'
			WHERE
				id=".$iBerichtID."
			LIMIT 1;";
		return $this->_db->query($sMessageQuery);
	}
	function editMessage($iBerichtID, $titel, $tekst, $bbcode_uid, $prive=false, $verborgen=false){
		$iBerichtID=(int)$iBerichtID;
		$titel=$this->_db->escape($titel);
		$tekst=$tekst;
		if($prive){$prive=1; }else{ $prive=0; }
		if($verborgen){$verborgen=1; }else{ $verborgen=0; }
		$sMessageQuery="
			UPDATE
				nieuws
			SET
				titel='".$titel."', 
				tekst='".$tekst."', 
				bbcode_uid='".$bbcode_uid."', 
				prive='".$prive."', 
				verborgen='".$verborgen."'
			WHERE
				id=".$iBerichtID."
			LIMIT 1;";
		return $this->_db->query($sMessageQuery);
	}
	function isNieuwsMod(){ return $this->_lid->hasPermission('P_NEWS_MOD');}
}

?>
