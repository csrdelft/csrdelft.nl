<?php
/*
 * class.categorie.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
class DocumentenCategorie{

	private $ID;
	private $naam;
	private $zichtbaar=1;
	private $documenten=array();

	public function __construct($catID=0, $loadChildren=-1){
		$this->load($catID, $loadChildren);
	}
	/*
	 * DocumentCategorie inladen.
	 *
	 * int catID			In te laden categorie, 0= een nieuwe categorie
	 * int loadChildren		-1	Geen kinderen inladen.
	 * 						0	Alle kinderen inladen.
	 * 						>0	Dit aantal kinderen wordt ingeladen.
	 */
	public function load($catID=0, $loadChildren=0){
		$this->ID=(int)$catID;
		if($this->getID()!=0){
			$db=MySql::instance();
			//gegevens over de categorie ophalen.
			$query="SELECT ID, naam, zichtbaar FROM documentcategorie WHERE ID=".$this->getID();
			$categorie=$db->query2array($query);
			$this->naam=$categorie['naam'];
			$this->zichtbaar=$categorie['zichtbaar'];

			$this->loadChildren($loadChildren);
		}
	}
	public function loadChildren($loadChildren){
		//kindertjes ophalen.
		if($loadChildren>=0){
			$query="SELECT ID FROM document WHERE catID=".$this->getID();
			if($loadChildren>0){
				$query.=' LIMIT '.$loadChildren;
			}
			$result=$db->query($query);
			while($doc=$db->next($result)){
				$this->documenten[]=new Document($doc['ID']);
			}
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
					naam, zichtbaar
				)VALUES(
					'".$db->escape($this->getNaam())."',
					".$this->getZichtbaar()."
				);";
		}else{
			$query="
				UPDATE documentcategorie SET
					naam='".$db->escape($this->getNaam())."',
					zichtbaar=".$this->getZichtbaar()."
				WHERE ID=".$this->getID().";";
		}
		return $db->query($query);
	}

	public function getID(){		return $this->ID; }
	public function getNaam(){		return $this->naam; }

	public function getDocumenten(){return $this->documenten; }
	public static function exists($catID){
		$cat=new DocumentenCategorie((int)$catID);
		return $cat->getID()!=0;
	}
}
?>
