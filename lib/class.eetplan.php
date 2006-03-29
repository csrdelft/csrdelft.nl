<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.eetplan.php
# -------------------------------------------------------------------
# Verzorgt het opvragen van eetplangegevens
# -------------------------------------------------------------------
# Historie:
# 01-10-2005 Jieter
# . gemaakt
#

require_once ('class.mysql.php');

class Eetplan {
	
	var $_db;
	var $_lid;
	
	function Eetplan(&$lid, &$db){
		# databaseconnectie openen
		$this->_lid =& $lid;
		$this->_db =& $db;
	}
	
	
	function getEetplan(){
		//huizen laden
		$rEetplan=$this->_db->select("
			SELECT
  			uid, huis, avond
			FROM
				eetplan
			ORDER BY
 				uid, avond;"
			);
		$aEetplan=array();
		$aEetplanRegel=array();
		while($aEetplanData=$this->_db->next($rEetplan)){
			//nieuwe regel beginnen als nodig
			if($aEetplanData['avond']==1){
				$aEetplan[]=$aEetplanRegel;
				$aEetplanRegel=array();
				//eerste element van de regel is het uid
				$aEetplanRegel[]=$aEetplanData['uid'];
			}
			$aEetplanRegel[]=$aEetplanData['huis'];
		}
		//ook de laaste regel toevoegen
		$aEetplan[]=$aEetplanRegel;
		//eerste regel eruit slopen, die is toch nutteloos.
		unset($aEetplan[0]);
		return $aEetplan;
	}
	
	function getEetplanVoorPheut($iPheutID){
		$sEetplanQuery="
			SELECT DISTINCT
				eetplan.avond AS avond,
				eetplanhuis.id AS huisID,
				eetplanhuis.naam AS huisnaam, 
				eetplanhuis.adres AS huisadres,
				eetplanhuis.telefoon AS telefoon
			FROM
				eetplanhuis, eetplan
			WHERE
				eetplan.huis=eetplanhuis.id AND
				eetplan.uid='".$iPheutID."'
			ORDER BY
				eetplan.avond;";
		$rEetplanVoorPheut=$this->_db->select($sEetplanQuery);
		if($this->_db->numRows($rEetplanVoorPheut)==0){
			//deze feut bestaat niet
			return false;
		}else{
			$aEetplan=array();
			while($aEetplanData=$this->_db->next($rEetplanVoorPheut)){
				$aEetplan[]=$aEetplanData;
			}
			return $aEetplan;
		}
	}
		
	function getEetplanVoorHuis($iHuisID){
		$sEetplanQuery="
			SELECT DISTINCT
				eetplan.avond AS avond,
				eetplanhuis.naam AS huisnaam, 
				eetplanhuis.adres AS huisadres,
				eetplanhuis.telefoon AS telefoon,
				eetplan.uid AS pheut
			FROM
				eetplanhuis, eetplan
			WHERE
				eetplan.huis=eetplanhuis.id AND
				eetplanhuis.id=".$iHuisID."
			ORDER BY
				eetplan.avond;";
		$rEetplanVoorHuis=$this->_db->select($sEetplanQuery);
		if($this->_db->numRows($rEetplanVoorHuis)==0){
			//geen huis met dit ID
			return false;
		}else{
			$aEetplan=array();
			while($aEetplanData=$this->_db->next($rEetplanVoorHuis)){
				$aEetplan[]=$aEetplanData;
			}
			return $aEetplan;
		}
	}
	
	
	function getDatum($iAvond){
		$aAvonden=array(
			'4-10 2005',
			'1-11 2005',
			'29-11 2005',
			'7-2 2006',
			'28-2 2006',
			'28-3 2006',
			'25-4 2006',
			'6-6 2006');
		return $aAvonden[$iAvond-1];
	}
	
	function getHuizen(){
		$sHuizenQuery="
			SELECT DISTINCT
				id AS huisID, 
				naam AS huisNaam, 
				adres, 
				telefoon
			FROM
				eetplanhuis
			ORDER BY 
				id;";
		$rHuizen=$this->_db->select($sHuizenQuery);
		while($aHuizenData=$this->_db->next($rHuizen)){
			$aHuizen[]=$aHuizenData;
		}
		return $aHuizen;
	}	
	function getPheutNaam($iPheutID){
		$sPheutQuery="
			SELECT
				voornaam, tussenvoegsel, achternaam, telefoon, mobiel
			FROM
				lid
			WHERE
				uid='".$iPheutID."'
			LIMIT 1;";
		$rPheutNaam=$this->_db->select($sPheutQuery);
		$aPheutNaam=$this->_db->next($rPheutNaam);
		$aReturnPheut['naam']=$aPheutNaam['voornaam'].' '.$aPheutNaam['tussenvoegsel'].' '.$aPheutNaam['achternaam'];
		$aReturnPheut['telefoon']=$aPheutNaam['telefoon'];
		$aReturnPheut['mobiel']=$aPheutNaam['mobiel'];
		return $aReturnPheut;				
	}
	
}
?>
