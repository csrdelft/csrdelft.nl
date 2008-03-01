<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.commissie.php
# -------------------------------------------------------------------
# Regelt de Commissies en Commissieleden tabellen van de database
# -------------------------------------------------------------------


require_once ('class.mysql.php');

class Commissie {

	private $cie=array(); 		# array met de geladen commissie

	
	function Commissie($cie){
		$this->_lid=Lid::get_lid();
		$this->load($cie);
	}

	public function load($cie) {
		$db=MySql::get_MySql();
		$cie=trim($cie);
		$filter='';
		if(preg_match("/^\d+$/", $cie)){
			$filter="id=".$cie;
		}elseif(preg_match("/^\w+$/", $cie)){
			$filter="naam='".$cie."'";
		}
		
		$result = $db->query("
			SELECT id, naam, stekst, titel, tekst, link
			FROM commissie 
			WHERE ".$filter." 
			LIMIT 1;");
		if($result !== false AND $db->numRows($result) > 0) {
			$this->cie = $db->next($result);
		}else{
			echo 'Commissie met de naam "'.$cie.'" niet gevonden';
			exit;
		}		
	}
	public function save(){
		$db=MySql::get_MySql();
		$query="
			UPDATE commissie 
			SET tekst='".$db->escape($this->cie['tekst'])."',
				stekst='".$db->escape($this->cie['stekst'])."',
				link='".$db->escape($this->cie['link'])."'
			WHERE id=".$this->cie['id']."
			LIMIT 1;";
		return $db->query($query);
	}
	
	function getId(){ return $this->cie['id']; }
	function getCommissie() { return $this->cie; }
	function getNaam(){ return $this->cie['naam']; }
	
	function setTekst($tekst){
		$this->cie['tekst']=$tekst;
	}
	function setStekst($stekst){
		$this->cie['stekst']=$stekst;
	}
	function setLink($link){
		if(url_like($link)){
			$this->cie['link']=$link;
		}
	}
	
	function magBewerken(){
		$lid=Lid::get_lid();
		if($lid->hasPermission('P_LEDEN_MOD')){
			return true;
		}else{
			$db=MySql::get_MySql();
			$sOpControle="
				SELECT uid 
				FROM commissielid 
				WHERE uid='".$lid->getUid()."' 
				  AND cieid=".$this->getId()." 
				  AND op='1'
				LIMIT 1;";
			$rOpControle=$db->query($sOpControle);
			return $db->numRows($rOpControle)==1;
		}
	}
	
	
	
	
	
	function verwijderLid( $uid){
		$lid=Lid::get_lid();
		if($lid->isValidUid($uid)){
			$db=MySql::get_MySql();
			$sVerwijderen="
				DELETE FROM 
					commissielid
				WHERE
					cieid=".$this->getId()."
				AND
					uid='".$uid."' 
				LIMIT 1;";
			return $db->query($sVerwijderen);
		}else{
			return false;
		}
	}
	function addLid($uid, $functie=''){
		$db=MySql::get_MySql();
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
			SELECT uid
			FROM commissielid
			WHERE cieid=".$this->getId()."
			  AND uid='".$uid."'
			LIMIT 1;";
		$rDubbelControle=$db->query($sDubbelControle);
		if($db->numRows($rDubbelControle)==0){
			$sCieQuery="
				INSERT INTO
					commissielid
				(
					cieid, uid, op, functie, prioriteit
				) VALUES (
					".$this->getId().", '".$uid."', '".$op."', '".$functie."', ".$prioriteit."
				)";
			return $db->query($sCieQuery);
		}else{ 
			return false; 
		}
	}
	
	public static function getLeden($cie){
		$db=MySql::get_MySql();
		$cie=(int)$cie;
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
				lid.soccieSaldo AS soccieSaldo
			FROM
				commissielid			
			LEFT JOIN
				lid ON commissielid.uid=lid.uid
			WHERE
				commissielid.cieid=".$cie."
			ORDER BY
				commissielid.prioriteit,
				lid.achternaam;";
		$rCieLeden=$db->select($sCieQuery);
		
		if($rCieLeden!==false ){
			if($db->numRows($rCieLeden)>0){
				while($aCieLid=$db->next($rCieLeden)){
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
	
	# haalt gegevens over alle commissies op voor de overzichtspagina
	public static function getOverzicht() {
		$db=MySql::get_MySql();
		
		$cieoverzicht = array();
		$result = $db->select("SELECT id, naam, stekst, titel FROM commissie ORDER BY naam;");
		if ($result !== false and $db->numRows($result) > 0){
			while ($cie = $db->next($result)){
				$cieoverzicht[] = $cie;
			}
		}
		return $cieoverzicht;
	}
	# haalt de commissies voor een bepaald lid op
	public static function getCieByUid($uid){
		$db=MySql::get_MySql();
		$cies = array();
		$result = $db->select("
			SELECT id, naam 
			FROM commissie 
			WHERE id IN ( 
				SELECT cieid FROM commissielid WHERE uid = '".$uid."'
			)
			ORDER BY naam;");
		if ($result !== false and $db->numRows($result) > 0){
			$cies=$db->result2array($result);
		}
		return $cies;
	}
}


?>
