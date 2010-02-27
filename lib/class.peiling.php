<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# --------------------------------------------------------------------------
# class.peiling.php
# --------------------------------------------------------------------------
# Verzorgt het opvragen en opslaan van peilingen en stemmen in de database.
# --------------------------------------------------------------------------
class Peiling {
	private $id=0;
	
	function __construct($init){		
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
				peiling.id = ".$iPeilingID.';';

		$db = MySql::instance();
		$rPeiling=$db->query($sPeilingQuery);
		return $db->next($rPeiling);
		
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
		$db = MySql::instance();
		$rPeiling=$db->query($sPeilingQuery);
		return $db->result2array($rPeiling);		
	}
	
	public function magStemmen (){
		$iPeilingID=(int)$this->id;
		if(!LoginLid::instance()->hasPermission('P_LOGGED_IN')){
			return false;
		}
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
		$db = MySql::instance();
		$rPeiling=$db->query($sPeilingQuery);
		$magStemmen =$db->numRows($rPeiling)==0; 
		return $magStemmen;	
	}
	
	public function stem($iOptie){		
		$iPeilingID = (int)$this->id;
		$iOptie = (int)$iOptie;
		if($this->magStemmen()){
			return $this->addStem($iOptie);
		}
		return false;
	}
	//UPDATE `peilingoptie` SET `stemmen`=1 WHERE `id`=3
	private function addStem($iOptie){
		$sUpdate = "
			UPDATE 
				peilingoptie 
			SET 
				stemmen = stemmen + 1
			WHERE 
				`id`=".$iOptie;		
		$sUpdate.=';';
		$db = MySql::instance();
		
		$rUpdate = $db->query($sUpdate);
		$rLog = $this->logStem();		
		return $rUpdate && $rLog;
	}
	//INSERT INTO `peiling_stemmen` (`peilingid`,`uid`) VALUES (2,'x101')
	private function logStem(){
		$uid = LoginLid::instance()->getUid();
		$iPeilingID = $this->id;
		$sInsert = "
			INSERT INTO 
				peiling_stemmen 
				(peilingid, uid) 
			VALUES 
				(".$iPeilingID.",'".$uid."')";
		$db = MySql::instance();
		$r = $db->query($sInsert);
		return $r;
	}
	
	public function deletePeiling(){
		if($this->magBewerken()){
			return $this->delete();
		}
		return 0;
	}
	

	private function delete(){
		$pid = $this->id;
		$db = MySql::instance();
		
		$sDelete = "DELETE FROM `peiling` WHERE `id`=".$pid.";";
		$rDeletePeiling = $db->query($sDelete);
		
		$sDeleteOpties = " DELETE FROM  `peilingoptie` WHERE `peilingid`=".$pid.";";
		$rDeleteOpties = $db->query($sDeleteOpties);
		
		$sDeleteLog="DELETE FROM `peiling_stemmen` WHERE `peilingid`=".$pid.";";
		$rDeleteLog = $db->query($sDeleteLog);
		
		return $rDeletePeiling && $rDeleteOpties && $rDeleteLog;
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
		$db = MySql::instance();
		
		$sCreate = "
			INSERT INTO 
				`peiling` 
				(`id`,`titel`,`tekst`) 
			VALUES 
				(NULL,'".$db->escape($titel)."','".$db->escape($verhaal)."')
			";
		$r = $db->query($sCreate);
		if(!$r){
			return NULL;
		}
		$pid = $db->insert_id();
		
		foreach($opties as $optie){
			$sCreateOptie = "
				INSERT INTO 
					`peilingoptie` 
					(`id`,`peilingid`,`optie`,`stemmen`) 
				VALUES 
					(NULL,".$pid.",'".$db->escape($optie)."',0)
				";
			$r = $db->query($sCreateOptie);
		}
		return $pid;
	}
	
	public static function magBewerken(){
		$magBewerken = false;
		 //Elk basfcie-lid heeft voorlopig peilingbeheerrechten.		
		if(LoginLid::instance()->hasPermission('P_ADMIN,groep:BASFcie')){
			$magBewerken = true; 
		}			
		return $magBewerken;	
	}
	
	public static function getLijst(){
		$sSelectQuery="
			SELECT
				*
			FROM
				peiling
			ORDER BY id DESC
			";
		
		$sSelectQuery.=';';
		$db=MySql::instance();
		$rPeilingen=$db->query($sSelectQuery);
		return $db->result2array($rPeilingen);
	}
}

?>
