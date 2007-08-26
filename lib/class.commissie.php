<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.commissie.php
# -------------------------------------------------------------------
# Regelt de Commissies en Commissieleden tabellen van de database
# -------------------------------------------------------------------


require_once ('class.mysql.php');

class Commissie {
	### private ###

	var $_cie; # commissie-beschrijving in de database
	var $_cieSaldo=0;
	
	var $_lid;
	var $_db;

	### public ###
	
	function Commissie(&$db, &$lid){
		$this->_db =& $db;
		$this->_lid =& $lid;
	}

	function loadCommissie($cie) {
		$cie=trim($cie);
		$filter='';
		if (preg_match("/^\d+$/", $cie)) $filter="id=".$cie;
		elseif(preg_match("/^\w+$/", $cie)) $filter="naam='".$cie."'";
		
		$result = $this->_db->query("
			SELECT 
				id, naam, stekst, titel, tekst, link
			FROM commissie 
			WHERE ".$filter." 
			LIMIT 1;");
		# eerst de opgegeven naam proberen...
		if ($result !== false AND $this->_db->numRows($result) > 0) {
			$this->_cie = $this->_db->next($result);
		} else {
			echo 'Commissie met de naam "'.$cie.'" niet gevonden';
			exit;
		}		
	}
	function getCommissie() { return $this->_cie; }
	function getNaam(){ return $this->_cie['naam']; }
	
	# haalt gegevens over alle commissies op voor de overzichtspagina
	function getOverzicht() {
		$cieoverzicht = array();
		$result = $this->_db->select("SELECT id, naam, stekst, titel FROM commissie ORDER BY naam;");
		if ($result !== false and $this->_db->numRows($result) > 0)
			while ($cie = $this->_db->next($result)) $cieoverzicht[] = $cie;
		return $cieoverzicht;
	}
	function magBewerken(){
		if($this->_lid->hasPermission('P_LEDEN_MOD')){
			return true;
		}else{
			$cieID=0;
			if(isset($this->_cie['id'])){ $cieID=$this->_cie['id']; }
			//controleren of iemand commissie op is:
			$sOpControle="
				SELECT 
					uid 
				FROM 
					commissielid 
				WHERE 
					uid='".$this->_lid->getUid()."' AND cieid=".$cieID." AND op='1'
				LIMIT 1;";
			$rOpControle=$this->_db->query($sOpControle);
			if($this->_db->numRows($rOpControle)==1){
				return true;
			}else{
				return false;
			}
		}
	}
	
	
	function getCieByUid($uid) {
		$cies = array();
		$result = $this->_db->select("SELECT id, naam FROM commissie WHERE id IN ( SELECT cieid FROM commissielid WHERE uid = '{$uid}') ORDER BY `naam`");
		if ($result !== false and $this->_db->numRows($result) > 0)
			while ($cie = $this->_db->next($result)) $cies[] = $cie;
		return $cies;
	}
	function getCieLeden($iCieID){
		$this->_cieSaldo=0;
		$iCieID=(int)$iCieID;
		$sCieQuery="
			SELECT
				lid.uid AS uid,
				lid.voornaam AS voornaam, 
				lid.tussenvoegsel tussenvoegsel, 
				lid.achternaam AS achternaam,
				lid.status AS status,
				lid.geslacht AS geslacht,
				lid.postfix AS postfix,
				commissielid.functie AS functie, 
				socciesaldi.saldo AS soccieSaldo
			FROM
				commissielid			
			LEFT JOIN
				lid ON commissielid.uid=lid.uid
			LEFT JOIN
				socciesaldi ON commissielid.uid=socciesaldi.uid			
			WHERE
				commissielid.cieid=".$iCieID."
			ORDER BY
				commissielid.prioriteit,
				lid.achternaam;";
		$rCieLeden=$this->_db->select($sCieQuery);
		
		if($rCieLeden!==false ){
			if($this->_db->numRows($rCieLeden)>0){
				while($aCieLid=$this->_db->next($rCieLeden)){
					$this->_cieSaldo+=$aCieLid['soccieSaldo'];
					$aCieLedenReturn[]=array(
						'uid' => $aCieLid['uid'], 
						'voornaam' => $aCieLid['voornaam'],
						'tussenvoegsel' => $aCieLid['tussenvoegsel'],
						'achternaam' => $aCieLid['achternaam'],
						'status' => $aCieLid['status'],
						'functie'=> $aCieLid['functie'], 
						'geslacht' => $aCieLid['geslacht'],
						'postfix' => $aCieLid['postfix']);
				}
				return $aCieLedenReturn;
			}else{
				return 'Geen leden voor deze commissie in het gegevensbeest.';
			}
		}else{
			return false;
		}			
	}
	function verwijderCieLid($iCieID, $uid){
		$iCieID=(int)$iCieID;
		if(preg_match('/^\w{4}$/', $uid)){
			$sVerwijderen="
				DELETE FROM 
					commissielid
				WHERE
					cieid=".$iCieID."
				AND
					uid='".$uid."' 
				LIMIT 1;";
			return $this->_db->query($sVerwijderen);
		}else{
			return false;
		}
	}
	function addCieLid($iCieID, $uid, $functie=''){
		$iCieID=(int)$iCieID;
		$op=0;
		switch(strtolower(trim($functie))){
			case 'praeses':
			case 'archivaris':
				$prioriteit=1;
				$op=1;
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
		//controleren of iemand al in de commissie zit
		$sDubbelControle="
			SELECT 
				uid
			FROM
				commissielid
			WHERE 
				cieid=".$iCieID."
			AND
				uid='".$uid."'
			LIMIT 1;";
		$rDubbelControle=$this->_db->query($sDubbelControle);
		if($this->_db->numRows($rDubbelControle)==0){
			$sCieQuery="
				INSERT INTO
					commissielid
				(
					cieid, uid, op, functie, prioriteit
				) VALUES (
					".$iCieID.", '".$uid."', '".$op."', '".$functie."', ".$prioriteit."
				)";
			return $this->_db->query($sCieQuery);
		}else{ return false; }
	}
	function getCieSaldo(){ return $this->_cieSaldo; }
}


?>
