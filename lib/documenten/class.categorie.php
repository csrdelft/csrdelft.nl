<?php
/*
 * class.categorie.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
class DocumentenCategorie{

	private $catID;
	private $naam;
	private $documenten=array();

	public function __construct($catID=0){
		$this->load($catID);
	}
	public function load($catID=0){
		$this->catID=(int)$catID;
		$db=MySql::instance();

	}
	public function getID(){		return $this->ID; }
	public function getNaam(){		return $this->naam; }
}
?>
