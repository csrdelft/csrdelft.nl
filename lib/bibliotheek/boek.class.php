<?php
/*
 * boek.class.php	| 	Gerrit Uitslag
 *
 * boeken
 *
 */

class Boek{

	private $id=0;
	private $titel;
	private $auteur_id; //auteur_id of biebauteur.auteur
	private $categorie_id;//categorie_id of concat van 3x biebcategorie.categorie )
	private $uitgavejaar;
	private $uitgeverij;
	private $paginas;
	private $taal;
	private $isbn;
	private $code;

	public function __construct($init){
		$this->load($init);
	}

	public function load($init=0){
		if(is_array($init)){
			$this->array2properties($init);
		}else{
			$this->id=(int)$init;
			if($this->getID()==0){
				//Bij $this->ID==0 gaat het om een nieuw boek. Hier
				//zetten we de defaultwaarden voor het nieuwe boek.
				//$this->setPropss(..);
			}else{
				$db=MySql::instance();
				$query="
					SELECT id, titel, auteur_id, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code
					FROM biebboek
					WHERE ID=".$this->getID().";";
				$doc=$db->getRow($query);
				if(is_array($boek)){
					$this->array2properties($boek);
				}else{
					throw new Exception('load() mislukt. Bestaat het boek wel?');
				}
			}
		}

	}
	public function array2properties($array){
		$properties=array('id', 'titel', 'auteur_id', 'categorie_id', 'uitgavejaar', 'uitgeverij', 'paginas', 'taal', 'isbn', 'code');
		foreach($properties as $prop){
			if(!isset($array[$prop])){
				throw new Exception('Boekproperties-array is niet compleet: '.$prop.' mist.');
			}
			$this->$prop=$array[$prop];
		}
	}

	public function getID(){			return $this->id;}
	public function getTitel(){			return $this->titel;}
	public function getAuteur(){		return $this->auteur_id;}
	public function getRubriek(){		return $this->categorie_id;}
	public function getUitgavejaar(){	return $this->uitgavejaar;}
	public function getUitgeverij(){	return $this->uitgeverij;}
	public function getPaginas(){		return $this->paginas;}
	public function getTaal(){			return $this->taal;}
	public function getISBN(){			return $this->isbn;}
	public function getCode(){			return $this->code;}


	public function magVerwijderen(){
		return Loginlid::instance()->hasPermission('P_BIEB_MOD','groep:BASFCie');
	}
	public function magBewerken(){
		return $this->magVerwijderen() OR Loginlid::instance()->hasPermission('P_BIEB_EDIT');
	}

}

?>
