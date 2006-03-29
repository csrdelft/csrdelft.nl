<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.commissie.php
# -------------------------------------------------------------------
# Regelt de Commissies en Commissieleden tabellen van de database
#
# -------------------------------------------------------------------
# Historie:
# 31-12-2004 Hans van Kranenburg
# . gemaakt
#

require_once ('class.mysql.php');

class Commissie {
	### private ###

	var $_cie; # commissie-beschrijving in de database
	var $_db;

	### public ###
	
	function Commissie(&$db) {
		$this->_db =& $db;
	}

	function loadCommissie($cie) {
		if (!preg_match("/^\w+$/",$cie)) $cie = "Commissies";

		# eerst de opgegeven naam proberen...
		$result = $this->_db->select("SELECT * FROM `commissie` WHERE `naam` = '{$cie}'");
        if ($result !== false and $this->_db->numRows($result) > 0) {
			$this->_cie = $this->_db->next($result);
		} else {
			# anders de standaard-info
			$result = $this->_db->select("SELECT * FROM `commissie` WHERE `naam` = 'Commissies'");
        	if ($result !== false and $this->_db->numRows($result) > 0) {
				$this->_cie = $this->_db->next($result);
			} else die("Webmaster, ga die Commissietabel repareren met je donder!");
		}
	}
	function getCommissie() { return $this->_cie; }
	
	# haalt gegevens over alle commissies op voor de overzichtspagina
	function getOverzicht() {
		$cieoverzicht = array();
		$result = $this->_db->select("SELECT `id`, `naam`, `stekst`, `titel` FROM `commissie` ORDER BY `naam`");
		if ($result !== false and $this->_db->numRows($result) > 0)
			while ($cie = $this->_db->next($result)) $cieoverzicht[] = $cie;
		return $cieoverzicht;
	}
	
	function getCieByUid($uid) {
		$cies = array();
		$uid = (int)$uid;
		$result = $this->_db->select("SELECT `naam` FROM `commissie` WHERE `id` IN ( SELECT `cieid` FROM `commissielid` WHERE `uid` = '{$uid}') ORDER BY `naam`");
		if ($result !== false and $this->_db->numRows($result) > 0)
			while ($cie = $this->_db->next($result)) $cies[] = $cie;
		return $cies;
	}
	function getCieLeden($iCieID){
		$iCieID=(int)$iCieID;
		$sCieQuery="
			SELECT
				lid.uid AS uid,
				lid.voornaam AS voornaam, 
				lid.tussenvoegsel tussenvoegsel, 
				lid.achternaam AS achternaam,
				commissielid.functie AS functie
			FROM
				lid, commissielid
			WHERE
				commissielid.uid=lid.uid
			AND
				commissielid.cieid=".$iCieID."
			ORDER BY
				commissielid.prioriteit,
				lid.achternaam;";
		$rCieLeden=$this->_db->select($sCieQuery);
		if($rCieLeden!==false ){
			if($this->_db->numRows($rCieLeden)>0){
				while($aCieLid=$this->_db->next($rCieLeden)){
					$sNaam=$aCieLid['voornaam'].' ';
					if($aCieLid['tussenvoegsel']!='')
						$sNaam.=$aCieLid['tussenvoegsel'].' ';
					$sNaam.=$aCieLid['achternaam'];
					$aCieLedenReturn[]=array('uid' => $aCieLid['uid'], 'naam' => $sNaam, 'functie'=> $aCieLid['functie']);
				}
				return $aCieLedenReturn;
			}else{
				return 'Geen leden voor deze commissie in het gegevensbeest.';
			}
		}else{
			return false;
		}			
	}
	function addCieLid($iCieID, $uid, $functie=''){
		$iCieID=(int)$iCieID;
		switch(strtolower(trim($functie))){
			case 'praeses':
			case 'archivaris':
				$prioriteit=1;
			break;
			case 'fiscus':
			case 'redacteur':
			case 'bibliothecaris':
			case 'posterman':
			case 'techniek':
			case 'abactis':
				$prioriteit=2;
			break;
			case 'computeur':
			case 'statisticus': 
			case 'provisor': 
		  case 'internetman':
		  case 'bandleider':
				$prioriteit=3;
			break;
			case 'fotocommisaris':
				$prioriteit=4;
			break;
			case 'koemissaris':
			case 'lustrumverhaalschrijver':
			case 'stralerpheut':
			case 'regelneef':
				$prioriteit=8;
			break;
			case 'q.q.':
			case 'qq':
				$prioriteit=9;
				$functie='Q.Q.';
			break;
			default:
				$prioriteit=5;
			break;
		}
		$sCieQuery="
			INSERT INTO
				commissielid
			(
				cieid, uid, functie, prioriteit
			) VALUES (
				".$iCieID.", '".$uid."', '".$functie."', ".$prioriteit."
			)";
		return $this->_db->query($sCieQuery);
	}
}


?>
