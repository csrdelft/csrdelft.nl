<?php
/*
 * class.groep.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * een Groep-object bevat een groep met wat eigenschappen en een array met leden en eventueel functies.
 */
class Groep{
	
	private $groep;
	private $leden=null;
	
	public function __construct($init){
		if(is_int($init)){
			$this->load($init);
		}elseif(is_array($init)){
			$this->groep=array_get_keys($init[0], array('groepId', 'snaam', 'naam', 'sbeschrijving', 'beschrijving', 'zichtbaar'));
			foreach($init as $lid){
				if($lid['uid']!=''){
					$this->leden[$lid['uid']]=array_get_keys($lid, array('uid', 'op', 'functie'));
				}
			}
		}
	}
	public function load($groepId){
		$db=MySql::get_MySql();
		die('TODO: implement this!!!!1');
	}
	
	/*
	 * slaat groepinfo op, geen leden!
	 */
	public function save(){
		$db=MySql::get_MySql();
		$qSave="
			UPDATE groep SET 
				snaam='".$db->escape($this->getSname())."',
	 			naam='".$db->escape($this->getName())."',
				sbeschrijving='".$db->escape($this->getSbeschrijving())."',
				beschrijving='".$db->escape($this->getBeschrijving())."',
				zichtbaar='".$db->escape($this->getZichtbaar())."'
			WHERE id=".$this->getId()."
			LIMIT 1;";
		return $db->query($qSave);
	}
	
	public function getId(){			return $this->groep['groepId']; }
	public function getSnaam(){			return $this->groep['snaam']; }
	public function getNaam(){			return $this->groep['naam']; }
	public function getSbeschrijving(){	return $this->groep['sbeschrijving']; }
	public function getBeschrijving(){	return $this->groep['beschrijving']; }
	public function getZichtbaar(){		return $this->groep['zichtbaar']; }
	
	public function setSnaam($value){			$this->groep['snaam']=$value; }
	public function setNaam($value){			$this->groep['naam']=$value; }
	public function setSbeschrijving($value){	$this->groep['sbeschrijving']=$value; }
	public function setBeschrijving($value){	$this->groep['beschrijving']=$value; }
	public function setZichtbaar($value){		$this->groep['zichtbaar']=$value; }
	
	public function isLid($uid){	return isset($this->leden[$uid]); }
	public function getLeden(){		return $this->leden; }
	
	public function magBewerken(){
		$lid=Lid::get_lid();
		return $lid->hasPermission('P_LEDEN_MOD') OR $this->isLid($lid->getUid());
	}
	
	//TODO: dit maken
	public static function loadBySname($sname){
		
	}
}
?>
