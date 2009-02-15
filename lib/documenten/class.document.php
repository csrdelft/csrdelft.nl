<?php
/*
 * class.document.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */

class Document{

	private $ID=0;
	private $naam;
	private $catID;
	private $bestandsnaam;
	private $size=0;
	private $mimetype='application/octet-stream';
	private $toegevoegd;
	private $eigenaar;

	public function __construct(){
		$this->toegevoegd=getDateTime();
	}

	public function load($init){
		if(is_array($init)){
			$this->array2properties($init);
		}else{
			$this->ID=(int)$init;
			if($this->getID()==0){
				$this->setToegevoegd(getDateTime());
			}else{
				$db=MySql::instance();
				$query="
					SELECT ID, naam, catID, bestandsnaam, size, mimetype, toegevoegd, eigenaar
					FROM document WHERE ID=".$this->getID().";";
				$doc=$db->query2array($query);
				if(is_array($doc)){
					$this->array2properties($doc);
				}else{
					return false;
				}
			}
		}

	}
	public function array2properties($array){
		$properties=array('ID', 'naam', 'catID', 'bestandsnaam', 'size', 'mimetype', 'toegevoegd', 'eigenaar');
		foreach($properties as $prop){
			if(!isset($array[$prop])){
				return false;
			}
			$this->$prop=$array[$prop];
		}
	}
	public function save(){
		$db=MySql::instance();
		if($this->getID()==0){
			$query="
				INSERT INTO document (
					naam, catID, bestandsnaam, size, mimetype, toegevoegd, eigenaar
				)VALUES(
					'".$db->escape($this->getNaam())."',
					".$this->getCatID().",
					'".$db->escape($this->getBestandsnaam())."',
					".$this->getSize().",
					'".$db->escape($this->getMimetype())."',
					'".$this->getToegevoegd()."',
					'".$this->getEigenaar()."'
				);";
		}else{
			$query="
				UPDATE document SET
					naam='".$db->escape($this->getNaam())."',
					catID=".$this->getCatID().",
					bestandsnaam='".$db->escape($this->getBestandsnaam())."',
					size=".$this->getSize().",
					mimetype='".$db->escape($this->getMimetype())."',
					toegevoegd='".$this->getToegevoegd()."',
					eigenaar='".$this->getEigenaar()."'
				WHERE ID=".$this->getID().";";
		}
		if($db->query($query)){
			if($this->getID()==0){
				$this->ID=$db->insert_id();
			}
			return true;
		}
		return false;
	}
	public function getID(){		return $this->ID; }
	public function getNaam(){		return $this->naam; }
	public function getCatID(){		return $this->catID; }
	public function getCategorie(){ return new DocumentCategorie($this->getCatID()); }
	public function getSize(){		return $this->size; }
	public function getMimetype(){	return $this->mimetype;	}
	public function getToegevoegd(){return $this->toegevoegd; }
	public function getEigenaar(){	return $this->eigenaar;	}

	public function setNaam($naam){
		$this->naam=$naam;
	}
	public function isEigenaar($uid=null){
		if($uid==null){ Lid::instance()->getUid(); }
		return $uid==$this->getEigenaar();
	}
}

?>
