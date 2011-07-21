<?php
/*
 * Catalogus
 *
 *
 */

class Catalogus{

	private $boeken=null;

//	public function __construct(){
		
//	}

	/*
	 * De boeken ophalen. 
	 * Boeken worden niet automagisch geladen, enkel bij het opvragen
	 * voor auteur en categorie worden een string opgeslagen ipv id's
	 * via count() of getDocumenten()
	 */
	public function loadBoeken(){
		$db=MySql::instance();
		$query="
			SELECT DISTINCT
				b.id , b.titel , b.uitgavejaar , b.uitgeverij , b.paginas, 
				b.taal, b.isbn, b.code, a.auteur,
				CONCAT(c1.categorie, ' - ',
					c2.categorie, ' - ',
					c3.categorie) AS categorie
			FROM 
				biebboek b, biebauteur a, biebexemplaar e, biebcategorie c1, biebcategorie c2, biebcategorie c3
			WHERE
				a.id = b.auteur_id AND
				b.id = e.boek_id AND
				c3.id = b.categorie_id AND
				c1.id = c2.p_id AND
				c2.id = c3.p_id 
			ORDER BY titel DESC";
		$result=$db->query($query);
		echo mysql_error();
		if($db->numRows($result)>0){
			while($boek=$db->next($result)){
				$this->boeken[]=new Boek($boek);
			}
		}else{
			return false;
		}
		return $db->numRows($result);
	}

	public function count(){
		if($this->boeken===null){
			$this->loadBoeken();
		}
		return count($this->boeken);
	}

	public function getBoeken($force=false){
		if($this->boeken===null OR $force){
			$this->loadBoeken();
		}
		return $this->boeken; 
	}
//	public function getBoek(){
//		return print_r($this->boeken[40]); 
//	}

	public static function getAllValuesOfProperty($key){
		$allowedkeys = array('id', 'titel', 'uitgavejaar', 'uitgeverij', 'paginas', 'taal', 'isbn', 'code');
		if(in_array($key, $allowedkeys)){
			$db=MySql::instance();
			$query="
				SELECT DISTINCT ".$db->escape($key)."
				FROM biebboek;";
			$result=$db->query($query);
			echo mysql_error();
			if($db->numRows($result)>0){
				while($prop=$db->next($result)){
					$properties[]=$prop[$key];
				}
				sort($properties);
				return array_filter($properties);
			}
		}
		echo 'boeh';
		return array();
	}
	public static function existsProperty($key,$value){
		$return = false;
		switch ($key) {
			case 'titel':
			case 'isbn':
				$return = in_array($value, Catalogus::getAllValuesOfProperty($key));
				break;
			case 'rubriek':
				$return = in_array($value, Rubriek::getAllRubriekIds());
				break;
			case 'auteur':
				$return = in_array($value, Auteur::getAllAuteurIds());
				break;
		}
		return $return;
	}

}
