<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.woonoord.php
# -------------------------------------------------------------------
#
# -------------------------------------------------------------------
# Historie:
# 28-08-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.mysql.php');

class Woonoord {
	### private ###
	var $_db;
	var $_woonoord;
	var $_lid;

	### public ###
	
	
	function Woonoord(&$db, &$lid) {
		$this->_db =& $db;
		$this->_lid =& $lid;
	}

	function isLid(){ return $this->_lid->hasPermission('P_LEDEN_READ'); }
	function magBewerken($iWoonoordID){
		if($this->_lid->hasPermission('P_LEDEN_MOD')){
			return true;
		}else{
			$iWoonoordID=(int)$iWoonoordID;
			$sIsBewoner="
				SELECT
					uid
				FROM 
					bewoner	
				WHERE
					woonoordid=".$iWoonoordID."
				AND
					uid=".$this->_lid->getUid()."
				LIMIT 1;";
			$rIsBewoner=$this->_db->query($sIsBewoner);
			if($this->_db->numRows($rIsBewoner)==1){
				return true;
			}else{
				return false;
			}
		}
	}
	function getWoonoorden(){
		$woonoorden=array();
		$sWoonoorden="
			SELECT
				woonoord.id AS id,
				woonoord.naam AS naam,
				woonoord.tekst AS tekst,
				woonoord.adres AS adres,
				woonoord.status AS status,
				woonoord.plaatje AS plaatje,
				woonoord.link AS link,
				lid.uid AS uid,
				lid.voornaam AS voornaam,
				lid.tussenvoegsel AS tussenvoegsel,
				lid.achternaam AS achternaam
			FROM 
				woonoord 
			LEFT JOIN 
				bewoner ON(woonoord.id=bewoner.woonoordid)
			INNER JOIN 
				lid ON(bewoner.uid=lid.uid)
			ORDER BY
				woonoord.sort, lid.achternaam, lid.voornaam;";
		$rWoonoorden=$this->_db->query($sWoonoorden);
		echo mysql_error();
		$iHuidigHuis=0;
		$sHuidigeStatus='';
		while($aWoonoord=$this->_db->next($rWoonoorden)){
			if($sHuidigeStatus!=$aWoonoord['status']){
				$sHuidigeStatus=$aWoonoord['status'];
			}
			if($iHuidigHuis!=$aWoonoord['id']){
				$iHuidigHuis=$aWoonoord['id']; 
				$woonoorden[$sHuidigeStatus][$iHuidigHuis]=array(
					'id'=>$aWoonoord['id'],
					'naam'=>$aWoonoord['naam'],
					'tekst'=>$aWoonoord['tekst'],
					'adres'=>$aWoonoord['adres'],
					'status'=>$aWoonoord['status'],
					'plaatje'=>$aWoonoord['plaatje'],
					'link'=>$aWoonoord['link'],
					'bewoners'=>array()	);
			}
			$woonoorden[$sHuidigeStatus][$iHuidigHuis]['bewoners'][]=array(
				'uid'=>$aWoonoord['uid'],
				'voornaam'=>$aWoonoord['voornaam'],
				'tussenvoegsel'=>$aWoonoord['tussenvoegsel'],
				'achternaam'=>$aWoonoord['achternaam'] );
		}
		return $woonoorden;
	}
			
	function getWoonoordByUid($uid) {
		# N.B. Bij het veranderen van bewoners en huizen moet opgelet worden dat een bewoner maar
		# in 1 woonoord tegelijk mag wonen!
		$result = $this->_db->select("
			SELECT id, naam
			FROM woonoord
			WHERE id IN ( SELECT woonoordid FROM bewoner WHERE uid = '{$uid}' )
		");
        if ($result !== false and $this->_db->numRows($result) == 1) {
			$record = $this->_db->next($result);
			return array('id' => $record['id'], 'naam' => $record['naam']);
		}
		
		# geen woonoord gevonden
		return false;	
	}
}

?>
