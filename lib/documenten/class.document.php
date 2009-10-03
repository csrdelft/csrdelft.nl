<?php
/*
 * class.document.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * bestanden worden allemaal in één map opgeslagen, met met hun documentID als prefix.
 *
 * Als men dus 2008-halfjaarschema.pdf upload komt er een bestand dat bijvoorbeeld
 * 1123_2008-halfjaarschema.pdf heet in de documentenmap te staan.
 *
 * In de database wordt de originele bestandsnaam opgeslagen, zonder prefix dus.
 *
 */

class Document{

	private $ID=0;
	private $naam;
	private $catID;			//DocumentID van de categorie van dit bestand
	private $categorie=null;//DocumentCategorie-object van dit bestand
	private $bestandsnaam;	//originele bestandsnaam zoals geupload
	private $size=0;		//bestandsafmeting in bytes
	private $mimetype='application/octet-stream'; //mime-type van het bestand
	private $toegevoegd;	//toevoegdatum
	private $eigenaar;		//uid van de eigenaar
	private $leesrechten='P_LEDEN_READ'; //rechten nodig om bestand te mogen downloaden

	public function __construct($init){
		$this->load($init);
	}

	public function load($init=0){
		if(is_array($init)){
			$this->array2properties($init);
		}else{
			$this->ID=(int)$init;
			if($this->getID()==0){
				//defaultwaarden voor een nieuw document
				$this->setToegevoegd(getDateTime());
			}else{
				$db=MySql::instance();
				$query="
					SELECT ID, naam, catID, bestandsnaam, size, mimetype, toegevoegd, eigenaar, leesrechten
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
		$properties=array('ID', 'naam', 'catID', 'bestandsnaam', 'size', 'mimetype', 'toegevoegd', 'eigenaar', 'leesrechten');
		foreach($properties as $prop){
			if(!isset($array[$prop])){
				throw new Exception('Array is niet compleet: '.$prop.' mist.');
			}
			$this->$prop=$array[$prop];
		}
	}
	public function save(){
		$db=MySql::instance();
		if($this->getID()==0){
			$query="
				INSERT INTO document (
					naam, catID, bestandsnaam, size, mimetype, toegevoegd, eigenaar, leesrechten
				)VALUES(
					'".$db->escape($this->getNaam())."',
					".$this->getCatID().",
					'".$db->escape($this->getBestandsnaam())."',
					".$this->getSize().",
					'".$db->escape($this->getMimetype())."',
					'".$this->getToegevoegd()."',
					'".$this->getEigenaar()."',
					'".$this->getLeesrechten()."'
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
					eigenaar='".$this->getEigenaar()."',
					leesrechten='".$this->getLeesrechten()."'
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
	public function getID(){			return $this->ID; }
	public function getNaam(){			return $this->naam; }

	public function getCatID(){			return $this->catID; }
	public function getCategorie($force=false){
		if($force OR $this->categorie==null){
			$this->categerie=new DocumentCategorie($this->getCatID());
		}
	}
	public function getBestandsnaam(){	return $this->bestandsnaam; }
	public function hasFile(){			return $this->getBestandsnaam()!=''; }
	public function getSize(){			return $this->size; }
	public function getMimetype(){		return $this->mimetype;	}
	public function getToegevoegd(){	return $this->toegevoegd; }
	public function getEigenaar(){
		return $this->eigenaar;	}

	public function setNaam($naam){
		$this->naam=$naam;
	}
	public function setCatID($catID){
		$this->catID=(int)$catID;
	}
	public function setToegevoegd($toegevoegd){
		$this->toegevoegd=$toegevoegd;
	}
	public function isEigenaar($uid=null){
		if($uid==null){ LoginLid::instance()->getUid(); }
		return $uid==$this->getEigenaar();
	}
	public function magBewerken(){
		return $this->isEigenaar() OR LoginLid::instance()->hasPermisson('P_DOCS_MOD');
	}
	public function getLeesrechten(){
		return $this->leesrechten;
	}
	public function magBekijken(){
		return LoginLid::instance()->hasPermission($this->getLeesrechten());
	}
}

?>
