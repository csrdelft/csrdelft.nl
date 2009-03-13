<?php
/*
 * class.mededelingcategorie.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */


class MededelingCategorie{

	private $id;
	private $naam;
	private $rank;
	private $plaatje;
	private $beschrijving;

	private $mededelingen=null;

	public function __construct($init){
		if(is_array($init)){
			$this->array2poperties($init);
		}else{
			$init=(int)$init;
			if($init!=0){
				$this->load($init);
			}else{
				//default waarden
				$this->rank=255;
			}
		}
	}
	public function load(){
		$db=MySql::instance();

	}
	public function save(){
		$db=MySql::instance();
		if($this->getId()==0){

		}else{

		}
	}
	public function delete(){

	}
	public function array2properties($array){
		$this->id=$array['id'];
		$this->naam=$array['naam'];
		$this->rank=$array['rank'];
		$this->plaatje=$array['plaatje'];
		$this->beschrijving=$array['beschrijving'];
	}

	public function getMededelingen($force=false){
		if($force OR $this->mededelingen===null){
			//load
			$this->loadMededelingen();
		}
		return $this->mededelingen;
	}

	public function getId(){ return $this->id; }
	public function getNaam(){ return $this->naam; }
	public function getRank(){ return $this->rank; }
	public function getPlaatje(){ return $this->plaatje; }
	public function getBeschrijving(){ return $this->beschrijving; }

	public static function getAll(){
		return MededelingCategorie::getCategorieen();
	}
	public static function getCategorieen(){
		$db=MySql::instance();
		$sCategorieQuery="
			SELECT id, naam, rank, plaatje, beschrijving
			FROM mededelingcategorie
			ORDER BY rank, id";
		$return='';
		foreach($db->query2array($sCategorieQuery) as $categorie){
			$return[]=new MededelingCategorie($categorie);
		}
		return $return;
	}
}
?>
