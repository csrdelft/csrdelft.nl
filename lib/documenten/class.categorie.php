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
	private $leesrechten='P_DOCS_READ';
	
	private $documenten=null;

	private $loadLimit=0;

	public function __construct($init){
		if(is_array($init)){
			$this->ID=$init['ID'];
			$this->naam=$init['naam'];
			$this->zichtbaar=$init['zichtbaar'];
			$this->leesrechten=$init['leesrechten'];
		}else{
			$this->load($init);
		}
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
				SELECT ID, naam, zichtbaar, leesrechten
				FROM documentcategorie WHERE ID=".$this->getID();
			$categorie=$db->query2array($query);
			if($categorie!==false){
				$this->naam=$categorie[0]['naam'];
				$this->zichtbaar=$categorie[0]['zichtbaar'];

			}else{
				//gevraagde categorie bestaat niet, we zet het ID weer op 0.
				$this->ID=0;
			}
		}
	}
	/*
	 * De onderhangende documenten ophalen. In $this->loadLimit wordt
	 * gebruikt in de LIMIT-clausule van de query.
	 * Documenten worden niet automagisch geladen, enkel bij het opvragen
	 * via getLast() of getDocumenten()
	 */
	public function loadDocumenten(){
		$db=MySql::instance();
		$query="
			SELECT ID, naam, catID, bestandsnaam, size, mimetype, toegevoegd, eigenaar, leesrechten
			FROM document WHERE catID=".$this->getID().' ORDER BY toegevoegd DESC ';
		if($this->loadLimit>0){
			$query.=' LIMIT '.$this->loadLimit;
		}
		$result=$db->query($query);
		if($db->numRows($result)>0){
			while($doc=$db->next($result)){
				$this->documenten[]=new Document($doc);
			}
		}else{
			return false;
		}
		return $db->numRows($result);
	}
	/*
	 * Slaat alleen de gegevens van een categorie op, DUS NIET de
	 * onderliggende documenten.
	 */
	public function save(){
		$db=MySql::instance();
		if($this->getID()==0){
			$query="
				INSERT INTO documentcategorie (
					naam, zichtbaar, leesrechten
				)VALUES(
					'".$db->escape($this->getNaam())."',
					".$this->getZichtbaar().",
					'".$db->escape($this->getLeesrechten())."'
				);";
		}else{
			$query="
				UPDATE documentcategorie SET
					naam='".$db->escape($this->getNaam())."',
					zichtbaar=".$this->getZichtbaar().",
					leesrechten='".$db->escape($this->getLeesrechten())."'
				WHERE ID=".$this->getID().";";
		}
		return $db->query($query);
	}

	public function getID(){		return $this->ID; }
	public function getNaam(){		return $this->naam; }
	public function getZichtbaaar(){return $this->zichtbaar; }
	public function isZichtbaar(){ 	return $this->zichtbaar==1; }
	public function getLeesrechten(){ return $this->leesrechten; }

	public function getLast($count){
		$this->loadLimit=(int)$count;
		$this->loadDocumenten();
		return $this->documenten;
	}
		

	public function getDocumenten($force=false){
		if($this->documenten===null OR $force){
			$this->loadDocumenten();
		}
		return $this->documenten; }

	public static function exists($catID){
		$cat=new DocumentenCategorie((int)$catID);
		return $cat->getID()!=0;
	}
	public static function getLeesrechtenVoorCatID($catID){
		$cat=new DocumentenCategorie((int)$catID);
		if($cat->getID()!=0){
			return $cat->getLeesrechten();
		}
		return false;
	}
	public static function getAll(){
		$db=MySql::instance();
		$query="SELECT ID, naam, zichtbaar, leesrechten
			FROM documentcategorie
			ORDER BY naam;";
		$result=$db->query($query);
		if($db->numRows($result)<=0){
			return false;
		}
		$return=array();
		while($categorie=$db->next($result)){
			$return[]=new DocumentenCategorie($categorie);
		}
		return $return;		
	}
}
?>
