<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.eetplan.php
# -------------------------------------------------------------------
# Verzorgt het opvragen van eetplangegevens
# -------------------------------------------------------------------


require_once ('class.mysql.php');

class Eetplan {
	
	var $_db;
	var $_lid;
	
	function Eetplan(){
		# databaseconnectie openen
		$this->_lid=Lid::get_lid();
		$this->_db=MySql::get_MySql();
	}
	
	
	function getEetplan(){
		//huizen laden
		$rEetplan=$this->_db->select("
			SELECT
  			eetplan.uid AS uid,
  			lid.voornaam AS voornaam,
  			lid.achternaam AS achternaam,
  			lid.tussenvoegsel AS tussenvoegsel,
  			eetplan.huis AS huis,
  			eetplan.avond AS avond
			FROM
				eetplan
			INNER JOIN lid ON(eetplan.uid=lid.uid)
			ORDER BY
 				lid.achternaam, avond;"
			);
		$aEetplan=array();
		$aEetplanRegel=array();
		while($aEetplanData=$this->_db->next($rEetplan)){
			//nieuwe regel beginnen als nodig
			if($aEetplanData['avond']==1){
				$aEetplan[]=$aEetplanRegel;
				$aEetplanRegel=array();
				//eerste element van de regel is het uid
				$aEetplanRegel[]=array(
					'uid' => $aEetplanData['uid'],
					'naam' => $this->_lid->getNaamLink($aEetplanData['uid'], 'full', false, $aEetplanData));
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
			'2-10-2007',
			'30-10-2007',
			'27-11-2007',
			'22-1-2008',
			'26-2-2008',
			'25-3-2008',
			'22-4-2008',
			'20-5-2008');
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
