<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# --------------------------------------------------------------------------
# class.peiling.php
# --------------------------------------------------------------------------
# Verzorgt het opvragen en opslaan van peilingen en stemmen in de database.
# Wordt o.a. door NieuwsContent gebruikt
# --------------------------------------------------------------------------
class Peiling {
	private $_db;
	
	private $id=0;
	
	function __construct($init){
		$this->_db=MySql::instance();
		$init = (int)$init;
		if($init!=0){
			//Check of deze peiling bestaat:Zo ja, sla id op.
			$this->id = $init; 
		}else{
			//Alleen geschikt voor het maken van een nieuwe peiling?			
		}
	}
	
	public function getID(){
		return $this->id;
	} 

	public function getPeiling(){
		$iPeilingID=(int)$this->id;

		$sPeilingQuery="
			SELECT
				*
			FROM
				peiling
			WHERE
				peiling.id = ".$iPeilingID;
		
		$sPeilingQuery.=';';
		$rPeiling=$this->_db->query($sPeilingQuery);
		return $this->_db->next($rPeiling);
		
	}

	public function getPeilingOpties(){
		$iPeilingID=(int)$this->id;

		$sPeilingQuery="
			SELECT
				*
			FROM
				peilingoptie
			WHERE
				peilingid = ".$iPeilingID."
			ORDER BY id ASC	
			";
		
		$sPeilingQuery.=';';
		$rPeiling=$this->_db->query($sPeilingQuery);
		return $this->_db->result2array($rPeiling);		
	}
	
	public function magStemmen (){
		$iPeilingID=(int)$this->id;
		$sUserID = LoginLid::instance()->getUid();
		$sPeilingQuery="
			SELECT
				*
			FROM
				peiling_stemmen
			WHERE
				peilingid = ".$iPeilingID." AND
				uid = '".$sUserID."'";
		
		$sPeilingQuery.=';';
		$rPeiling=$this->_db->query($sPeilingQuery);
		$magStemmen =$this->_db->numRows($rPeiling)==0; 
		return $magStemmen;	
	}
	
	public function stem($iOptie){
		$uid = LoginLid::instance()->getUid();
		$iPeilingID = (int)$this->id;
		$iOptie = (int)$iOptie;
		if($this->magStemmen()){
			$r1 = $this->addStem($iOptie);
			$r2 = $this->logStem($uid);
			return ($r1&&$r2);
		}
		return false;
	}
	//UPDATE `peilingoptie` SET `stemmen`=1 WHERE `id`=3
	private function addStem($iOptie){
		$iPeilingID = $this->id;
		//Get aantal stemmen
		$sPeilingQuery="
			SELECT
				stemmen
			FROM
				peilingoptie
			WHERE
				`id` = ".$iOptie;		
		$sPeilingQuery.=';';
		$rPeiling=$this->_db->query($sPeilingQuery);
		$rOptie = $this->_db->result2array($rPeiling);
		$stemmen = $rOptie[0]['stemmen'];
		
		//Tel deze stem erbij
		$stemtotaal = $stemmen + 1;		
		$sUpdate = "
			UPDATE 
				peilingoptie 
			SET 
				stemmen=".$stemtotaal." 
			WHERE 
				`id`=".$iOptie;		
		$sUpdate.=';';
		$r = $this->_db->query($sUpdate);
		return $r;
	}
	//INSERT INTO `peiling_stemmen` (`peilingid`,`uid`) VALUES (2,'x101')
	private function logStem($uid){
		$iPeilingID = $this->id;
		$sInsert = "
			INSERT INTO 
				peiling_stemmen 
				(peilingid, uid) 
			VALUES 
				(".$iPeilingID.",'".$uid."')";
		$r = $this->_db->query($sInsert);
		return $r;
	}
	
	public function deletePeiling($peilingID){
		$pid = (int) $peilingID;
		if(($pid == $this->id) && $this->magBewerken()){
			return $this->delete($pid);
		}
		return 0;
	}
	
	//DELETE FROM `peiling` WHERE `id`=1
	//DELETE FROM `peilingoptie` WHERE `peilingid`=1
	//DELETE FROM `peiling_stemmen` WHERE `peilingid`=1 
	private function delete($pid){
		$sDelete = "
			DELETE FROM 
				`peiling` 
			WHERE `id`=".$pid.";
			";
		$r1 = $this->_db->query($sDelete);
		$sDeleteOpties = "
			DELETE FROM 
				`peilingoptie` 
			WHERE `peilingid`=".$pid.";
			";
		$r2 = $this->_db->query($sDeleteOpties);
		$sDeleteOpties = "
			DELETE FROM 
				`peiling_stemmen` 
			WHERE `peilingid`=".$pid."
			";
		$r3 = $this->_db->query($sDeleteOpties);
		
		return $r1 && $r2 && $r3;
	}
	
	public function maakPeiling($properties){
		if($this->magBewerken() && is_array($properties)){			
			return $this->create($properties);
		}
		return 0;
	}
	
	//INSERT INTO `peiling` (`id`,`titel`,`tekst`) VALUES (NULL,'titel','verhaal')
	//INSERT INTO `peilingoptie` (`id`,`peilingid`,`optie`,`stemmen`) VALUES (NULL,pid,'optietekst',0)
	//Geeft het id van de nieuwe peiling terug, of NULL.
	private function create($properties){
		$titel = $properties['titel'];
		$verhaal = $properties['verhaal'];
		$opties;
		if(is_array($properties['opties'])){
			$opties = $properties['opties'];
		}
		
		$sCreate = "
			INSERT INTO 
				`peiling` 
				(`id`,`titel`,`tekst`) 
			VALUES 
				(NULL,'".$titel."','".$verhaal."')
			";
		$r = $this->_db->query($sCreate);
		if(!$r){
			return NULL;
		}
		$pid = $this->_db->insert_id();
		
		foreach($opties as $optie){
			$sCreateOptie = "
				INSERT INTO 
					`peilingoptie` 
					(`id`,`peilingid`,`optie`,`stemmen`) 
				VALUES 
					(NULL,".$pid.",'".$optie."',0)
				";
			$r = $this->_db->query($sCreateOptie);
		}
		return $pid;
	}
	
	public static function magBewerken(){
		require_once('groepen/class.groep.php');
		$sUserID = LoginLid::instance()->getUid();
		$magBewerken = false;
		$basfgroep = new Groep('BASFcie');  //Elk basfcie-lid heeft voorlopig peilingbeheerrechten.
		if(LoginLid::instance()->hasPermission('P_ADMIN') || ($basfgroep->isLid($sUserID)) ){
			$magBewerken = true; 
		}			
		return $magBewerken;	
	}
	
	public static function getLijst(){
		$sSelectQuery="
			SELECT
				*
			FROM
				peiling";
		
		$sSelectQuery.=';';
		$_db=MySql::instance();
		$rPeilingen=$_db->query($sSelectQuery);
		return $_db->result2array($rPeilingen);
	}
}

?>