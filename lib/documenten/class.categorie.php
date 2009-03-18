<?php
/*
 * class.categorie.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once 'class.document.php';

class DocumentenCategorie{

	private $ID;
	private $naam;
	private $zichtbaar=1;
	private $permissie='P_DOCS_READ';
	private $documenten=null;

	private $loadLimit=0;

	public function __construct($catID=0){
		$this->load($catID);
	}
	/*
	 * DocumentCategorie inladen.
	 *
	 * int catID			In te laden categorie, 0= een nieuwe categorie
	 */
	public function load($catID=0){
		$this->ID=(int)$catID;
		if($this->getID()!=0){
			$db=MySql::instance();
			//gegevens over de categorie ophalen.
			$query="
				SELECT ID, naam, zichtbaar, permissie
				FROM documentcategorie WHERE ID=".$this->getID();
			$categorie=$db->query2array($query);
			if($categorie!==false){
				$this->naam=$categorie['naam'];
				$this->zichtbaar=$categorie['zichtbaar'];

			}else{
				//gevraagde categorie bestaat niet, we zet het ID weer op 0.
				$this->ID=0;
			}
		}
	}
	/*
	 * De onderhangende documenten ophalen.
	 */
	public function loadDocumenten(){
		$query="
			SELECT ID, naam, catID, bestandsnaam, size, mimetype, toegevoegd, eigenaar
			FROM document WHERE catID=".$this->getID();
		if($this->loadLimit>0){
			$query.=' LIMIT '.$this->loadLimit;
		}
		$result=$db->query($query);
		while($doc=$db->next($result)){
			$this->documenten[]=new Document($doc);
		}
		return $db->numRows($result);
	}
	/*
	 * Slaat alleen de gegevens van een categorie op, niet de onderliggende documenten
	 */
	public function save(){
		$db=MySql::instance();
		if($this->getID()==0){
			$query="
				INSERT INTO documentcategorie (
					naam, zichtbaar, permissie
				)VALUES(
					'".$db->escape($this->getNaam())."',
					".$this->getZichtbaar().",
					'".$db->escape($this->getPermissie())."'
				);";
		}else{
			$query="
				UPDATE documentcategorie SET
					naam='".$db->escape($this->getNaam())."',
					zichtbaar=".$this->getZichtbaar().",
					permissie='".$db->escape($this->getPermissie())."'
				WHERE ID=".$this->getID().";";
		}
		return $db->query($query);
	}

	public function getID(){		return $this->ID; }
	public function getNaam(){		return $this->naam; }
	public function getZichtbaaar(){return $this->zichtbaar; }
	public function isZichtbaar(){ 	return $this->zichtbaar==1; }
	public function getPermissie(){ return $this->permissie; }

	public function getDocumenten($force=false){
		if($this->documenten===null OR $force){
			$this->loadDocumenten();
		}
		return $this->documenten; }

	public static function exists($catID){
		$cat=new DocumentenCategorie((int)$catID);
		return $cat->getID()!=0;
	}
	public static function getPermissieVoorCatID($catID){
		$cat=new DocumentenCategorie((int)$catID);
		if($cat->getID()!=0){
			return $cat->getPermissie();
		}
		return false;
	}
}
?>
