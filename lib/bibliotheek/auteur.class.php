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
			if(is_int($auteur) AND $auteur==0){
				throw new Exception('id = 0 bestaat niet. Auteur::construct()');
			}else{
				$db=MySql::instance();
				if(is_int($auteur)){
					$where="id=".(int)$auteur;
				}else{
					$where="auteur='".$db->escape($auteur)."'"; //is dit uniek? en niet gevonden is niet netjes afgevangen.
				}
				$query="
					SELECT id, auteur
					FROM biebauteur
					WHERE ".$where.";";
				$result=$db->getRow($query);
				if(is_array($result)){
					$this->array2properties($result);
				}else{
					$qSave="
						INSERT INTO biebauteur (
							auteur
						) VALUES (
							'".$db->escape($auteur)."'
						);";
					if($db->query($qSave)){
						//object Auteur vullen
						$this->id=$db->insert_id();
						$this->naam=$auteur;
					}else{
						throw new Exception('Fout in query, mysql gaf terug: '.mysql_error().' Auteur::construct()');
					}
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

	public static function getAllAuteurs($short=false){
		$db=MySql::instance();
		$query="
			SELECT id, auteur
			FROM biebauteur;";
		$result=$db->query($query);
		echo mysql_error();
		if($db->numRows($result)>0){
			while($auteur=$db->next($result)){
				if($short){
					$auteurs[]=$auteur['auteur'];
				}else{
					$auteurs[]=$auteur;
				}
			}
			sort($auteurs);
			return array_filter($auteurs);
		}else{
			return array();
		}
	}
}

?>
