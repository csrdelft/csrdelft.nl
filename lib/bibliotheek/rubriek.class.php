<?php
/*
 * rubriek.class.php	| 	Gerrit Uitslag
 *
 * rubriek
 *
 */
 
 class Rubriek{

	private $id=0;
	private $rubriek=array();
	private $blaat;
	public function __construct($init){
		$db=MySql::instance();
		$query="
			SELECT c3.id, c1.categorie AS cat1, c2.categorie AS cat2, c3.categorie AS cat3
			FROM biebcategorie c1, biebcategorie c2, biebcategorie c3
			WHERE c2.p_id = c1.id
			AND c3.p_id = c2.id
			AND c1.p_id =0
			AND c3.id = ".(int)$init.";";
		$categorie=$db->getRow($query);

		if(is_array($categorie)){
			$this->id=$categorie['id'];
			$this->rubriek=array( $categorie['cat1'], $categorie['cat2'], $categorie['cat3']);
		}else{
			throw new Exception('__contruct() mislukt. Bestaat de rubriek wel?');
		}
	}

	public function getID(){ return $this->id;}
	public function getRubriekArray(){ return $this->rubriek;}
	public function getRubrieken(){ return implode(" - ", $this->rubriek);}
	public function getRubriek(){ return $this->rubriek[2];}

	public static function getAllRubrieken($samenvoegen=false,$short=false){
		$db=MySql::instance();
		$query="
			SELECT c3.id, c1.categorie AS cat1, c2.categorie AS cat2, c3.categorie AS cat3
			FROM biebcategorie c1, biebcategorie c2, biebcategorie c3
			WHERE c2.p_id = c1.id
			AND c3.p_id = c2.id
			AND c1.p_id =0;";
		$result=$db->query($query);
		echo mysql_error();
		if($db->numRows($result)>0){
			while($categorie=$db->next($result)){
				if($samenvoegen){
					$samengevoegderubrieken = implode(" - ", array( $categorie['cat1'], $categorie['cat2'], $categorie['cat3']));
					if($short){
						$categorien[$categorie['id']]=$samengevoegderubrieken;
					}else{
						$categorien[]=array(
							'id'=>$categorie['id'], 
							'cat'=>$samengevoegderubrieken
						);
					}
				}else{
						$categorien[]=$categorie;
				}
			}
			return $categorien;
		}else{
			return array();
		}
	}
	public static function getAllRubriekIds(){
		$db=MySql::instance();
		$query="
			SELECT id
			FROM biebcategorie;";
		$result=$db->query($query);
		echo mysql_error();
		if($db->numRows($result)>0){
			while($catid=$db->next($result)){
				$catids[]=$catid['id'];
			}
			sort($catids);
			return array_filter($catids);
		}else{
			return array();
		}
	}
}

?>
