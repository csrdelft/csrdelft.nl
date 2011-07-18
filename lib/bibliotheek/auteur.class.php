<?php
/*
 * auteur.class.php	| 	Gerrit Uitslag
 *
 * auteur
 *
 */
 
 class Auteur{

	private $id=0;
	private $naam;


	public function __construct($auteur){

		if(is_array($auteur)){
			$this->array2properties($auteur);
		}else{
			if(is_int($auteur)){
				$where="id=".(int)$auteur;
			}else{
				$where="auteur='".$db->escape($auteur)."'"; //is dit uniek? en niet gevonden is niet netjes afgevangen.
			}

			if((int)$auteur==0){
				//Bij $this->ID==0 gaat het om een nieuw boek. Hier
				//zetten we de defaultwaarden voor het nieuwe boek.
				//$this->setPropss(..);
			}else{
				$db=MySql::instance();
				$query="
					SELECT id, auteur
					FROM biebauteur
					WHERE ".$where.";";
				$auteur=$db->getRow($query);
				if(is_array($auteur)){
					$this->array2properties($auteur);
				}else{
					throw new Exception('Auteur:: __construct() mislukt. Bestaat de Auteur wel?');
				}
			}
		}
	}

	private function array2properties($properties){
		$this->id = $properties['id'];
		$this->naam = $properties['auteur'];
	}

	public function getId(){			return $this->id;}
	public function getNaam(){		return $this->naam;}

	public function setNaam($naam){	$this->naam=$naam;}

}

?>
