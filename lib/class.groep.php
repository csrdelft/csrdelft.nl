<?php
/*
 * class.groep.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * een Groep-object bevat een groep met wat eigenschappen en een array met leden en eventueel functies.
 */
class Groep{
	
	private $groep=null;
	private $leden=null;
	
	public function __construct($init){
		if(is_int($init) OR is_string($init)){
			//we maken een nieuwe
			if($init===0){
				//dit zijn de defaultwaarden voor een nieuwe groep
				$this->groep=array('id'=>0, 'snaam'=>'', 'naam'=>'', 'sbeschrijving'=>'', 'beschrijving'=>'', 'zichtbaar'=>'zichtbaar');
			}else{
				$this->load($init);
			}
		}elseif(is_array($init) AND isset($init[0])){
			$this->groep=array_get_keys($init[0], array('groepId', 'snaam', 'naam', 'sbeschrijving', 'beschrijving', 'zichtbaar'));
			foreach($init as $lid){
				if($lid['uid']!=''){
					$this->leden[$lid['uid']]=array_get_keys($lid, array('uid', 'op', 'functie'));
				}
			}
		}
	}
	/*
	 * Laad een groep in aan de hand van het id of de snaam
	 * 
	 * @param	$groepId	integer groepId of string snaam
	 * @return	void
	 */
	public function load($groepId){
		$db=MySql::get_MySql();
		if(preg_match('/^\d+$/', $groepId)){
			$wherePart="groep.id=".(int)$groepId;
		}else{
			//een snaam is niet uniek. Enkel voor h.t. groepen is de snaam uniek, niet voor
			//o.t. vs. h.t. of bij o.t. onderling
			$wherePart="groep.snaam='".$db->escape($groepId)."' AND status='ht'";
		}
		$qGroep="
			SELECT 
				groep.id AS groepId, groep.snaam AS snaam, groep.naam AS naam,
				groep.sbeschrijving AS sbeschrijving, groep.beschrijving AS beschrijving, groep.zichtbaar AS zichtbaar,
				groeplid.uid AS uid, groeplid.op AS op, groeplid.functie AS functie, groeplid.prioriteit AS prioriteit,
				groeptype.id AS gtypeId, groeptype.naam AS gtype
			FROM groep
			LEFT JOIN groeplid ON(groep.id=groeplid.groepid)
			INNER JOIN groeptype ON(groep.gtype=groeptype.id)
			WHERE ".$wherePart."
			ORDER BY groeplid.prioriteit ASC;";
		$rGroep=$db->query($qGroep);
		while($aGroep=$db->next($rGroep)){
			//groepseigenschappen worden alleen de eerste iteratie opgeslagen
			if($this->groep===null){
				$this->groep=array_get_keys($aGroep, array('groepId', 'gtypeId', 'gtype', 'snaam', 'naam', 'sbeschrijving', 'beschrijving', 'zichtbaar'));
			}
			//en ook de leden inladen.
			if($aGroep['uid']!=''){
				$this->leden[$aGroep['uid']]=array_get_keys($aGroep, array('uid', 'op', 'functie'));
			}
		}
		
		}
	
	/*
	 * save().
	 * slaat groepinfo op, geen leden!
	 */
	public function save(){
		$db=MySql::get_MySql();
		if($this->getId()==0){
			//TODO maak INSERT-query
		}else{
			$qSave="
				UPDATE groep SET 
					snaam='".$db->escape($this->getSnaam())."',
		 			naam='".$db->escape($this->getNaam())."',
					sbeschrijving='".$db->escape($this->getSbeschrijving())."',
					beschrijving='".$db->escape($this->getBeschrijving())."',
					zichtbaar='".$db->escape($this->getZichtbaar())."'
				WHERE id=".$this->getId()."
				LIMIT 1;";
		}
		if($db->query($qSave)){
			//als het om een nieuwe groep gaat schrijven we het nieuwe id weg in de
			//instantie van het object, zodat we bijvoorbeeld naar dat nieuwe id kunnen refreshen.
			if($this->getId()==0){ 
				$this->groep['id']=$db->insert_id();
			}
			return true;
		}
		return false;
	}
	
	public function getType(){
		if(isset($this->groep['gtype'])){
			return $this->groep['gtype'];
		}
	}
	public function getId(){			return $this->groep['groepId']; }
	public function getSnaam(){			return $this->groep['snaam']; }
	public function getNaam(){			return $this->groep['naam']; }
	public function getSbeschrijving(){	return $this->groep['sbeschrijving']; }
	public function getBeschrijving(){	return $this->groep['beschrijving']; }
	public function getZichtbaar(){		return $this->groep['zichtbaar']; }
	
	public function setSnaam($value){			$this->groep['snaam']=trim($value); }
	public function setNaam($value){			$this->groep['naam']=trim($value); }
	public function setSbeschrijving($value){	$this->groep['sbeschrijving']=trim($value); }
	public function setBeschrijving($value){	$this->groep['beschrijving']=trim($value); }
	public function setZichtbaar($value){		$this->groep['zichtbaar']=trim($value); }
	
	public function isLid($uid){	return isset($this->leden[$uid]); }
	public function isOp($uid){		return $this->isLid($uid) AND $this->leden[$uid]['op']=='1'; }
	public function getLeden(){		return $this->leden; }
	
	public static function isAdmin(){		
		$lid=Lid::get_lid();
		return $lid->hasPermission('P_LEDEN_MOD');
	}
	public function magBewerken(){
		return $this->isAdmin() OR $this->isOp($lid->getUid());
	}
	
	function verwijderLid($uid){
		$lid=Lid::get_lid();
		if($lid->isValidUid($uid)){
			$db=MySql::get_MySql();
			$qVerwijderen="
				DELETE FROM 
					groeplid
				WHERE
					groepid=".$this->getId()."
				AND
					uid='".$uid."' 
				LIMIT 1;";
			return $db->query($qVerwijderen);
		}else{
			return false;
		}
	}
	function addLid($uid, $functie=''){
		$db=MySql::get_MySql();
		$op=0;
		switch(strtolower(trim($functie))){
			case 'praeses':	case 'archivaris': case 'werkgroepleider':
				$prioriteit=1;
				$op=1;
			break;
			case 'fiscus': case 'redacteur': case 'bibliothecaris':
			case 'posterman': case 'techniek': case 'abactis':
				$prioriteit=2;
			break;
			case 'computeur': case 'statisticus': case 'provisor': 
			case 'internetman': case 'bandleider':
				$prioriteit=3;
			break;
			case 'fotocommisaris':
				$prioriteit=4;
			break;
			case 'koemissaris': case 'stralerpheut': case 'regelneef':
				$prioriteit=8;
			break;
			case 'q.q.': case 'qq':
				$prioriteit=9;
				$functie='Q.Q.';
			break;
			default:
				$prioriteit=5;
			break;
		}
		if(!$this->isLid()){
			$sCieQuery="
				INSERT INTO commissielid
					( cieid, uid, op, functie, prioriteit )
				VALUES (
					".$this->getId().", '".$uid."', '".$op."', '".$functie."', ".$prioriteit."
				)";
			return $db->query($sCieQuery);
		}else{ 
			return false; 
		}
	}
}
?>
