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

	/*
	 * constructor
	 * Input $auteur: 
	 *  - array()		- maakt object met gegeven gegevens
	 *  - integer		- zoekt gegevens op adhv id
	 *  - integer& 0	- maakt leeg object
	 *  - string		- maakt object met gegeven auteurnaam met id=0
	 */
	public function __construct($auteur){
		if(is_array($auteur)){
			$this->setId($auteur['id']);
			$this->setNaam($auteur['auteur']);
		}else{
			if(is_int($auteur)){
				// opzoeken adhv id
				$this->setId($auteur);
				$this->setNaam($this->getNaamFromDb($auteur));
			}else{
				// er is een naam van auteur gegeven.
				if($auteur=='0'){
					$auteur='';
				}
				$this->setNaam(trim($auteur));
			}
		}
	}

	public function getId(){			return $this->id;}
	public function getNaam(){			return $this->naam;}

	private function setId($id){		$this->id=(int)$id;}
	public function setNaam($naam){		$this->naam=$naam;}

	/*
	 * Sla object Auteur op
	 * - Probeert eerst id bij auteur te zoeken
	 * - Voegt nieuwe auteurs toe
	 * - Verwijdert een niet meer gebruikte auteur
	 */
	public function save(){
		$naam = $this->getNaam();
		$id = $this->getId();

		//leeg object of staat de auteur al in de db?
		if(($id==0 AND $naam=='') OR $this->getNaamFromDb($id) == $naam){
			return true;

		}elseif($idnew=$this->existingAuteurNaam($naam)){
			//sla id van die bestaande auteur op in object Auteur
			$this->setId($idnew);
			//zijn er nog boeken met ons auteurid in db?
			if($id==0 OR $this->hasAuteurOtherBooks($id)){
				//het oude $id is nog in gebruik, dus niet weggooien
				return true;
			}else{
				//het oude $id is niet meer ingebruik, weg ermee
				return $this->delete($id);
			}
		}else{
			//zijn er boeken met ons auteurid in db?
			if($id==0 OR $this->hasAuteurOtherBooks($id)){
				//$id ingebruik, nieuwe auteur toevoegen
				return $this->insertDb();
			}else{
				//naamwijziging
				return $this->updateDb();
			}
		}
	}
	/*
	 * heeft de auteur boeken in de catalogus?
	 * 
	 * @param 	$id auteurid 
	 * 			of leeg: auteurid uit Boek halen
	 * @return	true minstens 1 keer gevonden
	 * 			false niet gevonden
	 */
	public function hasAuteurOtherBooks($id=null){
		if($id===null){
			$id=$this->getId();
		}
		$db=MySql::instance();
		$query="
			SELECT id, boek_id
			FROM biebboek
			WHERE auteur_id=".(int)$id.";";
		$result=$db->query($query);
		if($db->numRows($result)>0){
			return true;
		}else{
			return false;
		}
	}
	/*
	 * Verwijder auteur uit de database
	 * 
	 * @param 	$id auteurid
	 * 			of leeg: auteurid uit Boek halen
	 * @return	true gelukt
	 * 			false mislukt
	 */
	protected function delete($id=null){
		if($id===null){
			$id=$this->getId();
		}
		if($id==0){
			//$this->error.='Kan geen lege boek met id=0 wegkekken. Auteur::delete()';
			return false;
		}
		$db=MySql::instance();
		$qDeleteAuteur="DELETE FROM biebauteur WHERE id=".(int)$id." LIMIT 1;";
		return $db->query($qDeleteAuteur);
	}
	/*
	 * Zoekt naam bij een id in de database
	 * @param $id auteurid
	 * 			of leeg: auteurid uit Boek halen 
	 * @return	string auteursnaam
	 * 			of lege string bij geen resultaat
	 */
	public function getNaamFromDb($id=null){
		if($id===null){
			$id=$this->getId();
		}
		$db=MySql::instance();
		$query="
			SELECT id, auteur
			FROM biebauteur
			WHERE id=".(int)$id.";";
		$result=$db->query($query);
		if($db->numRows($result)>0){
			$auteur=$db->next($result);
			return $auteur['auteur'];
		}else{
			//bestaat niet (meer) of id=0: retourneer lege naam 
			return '';
		}
	}
	/*
	 * Zoekt naar exacte match van $naam
	 * 
	 * @param $naam 
	 * @return: 	auteurid 
	 * 				of false
	 */
	public function existingAuteurNaam($naam){
		$db=MySql::instance();
		$naam=$db->escape(trim($this->getNaam()));
		$query="
			SELECT id, auteur
			FROM biebauteur
			WHERE auteur='".$naam."';";
		$result=$db->query($query);
		if($db->numRows($result)>0){
			$auteur=$db->next($result);
			return $auteur['id'];
		}else{
			return false;
		}
	}
	/*
	 * update gegevens in db
	 * 
	 * @return	true gelukt
	 * 			false mislukt
	 */
	protected function updateDb(){
		$db=MySql::instance();
		$naam=$db->escape(trim($this->getNaam()));
		$qSave="
				UPDATE biebauteur SET
					auteur= '".$naam."'
				WHERE id= ".$this->getId()."
				LIMIT 1;";
		if($db->query($qSave)){
			return true;
		}else{
			return false;
		}
	}
	/*
	 * voegt auteur toe aan database
	 * 
	 * @return	true gelukt
	 * 			false mislukt
	 */
	protected function insertDb(){
		$db=MySql::instance();
		$qSave="
			INSERT INTO biebauteur (
				auteur
			) VALUES (
				'".$db->escape(trim($this->getNaam()))."'
			);";
		if($db->query($qSave)){
			//id opslaan in object Auteur
			$this->id=$db->insert_id();
			return true;
		}else{
			return false;
		}
	}
	/*
	 * Geeft alle auteurs in db
	 * 
	 * @return 	array met alle namen alfabetisch gesorteerd
	 * 			of lege array
	 */
	public static function getAllAuteurs(){
		$db=MySql::instance();
		$query="
			SELECT id, auteur
			FROM biebauteur;";
		$result=$db->query($query);
		echo mysql_error();
		if($db->numRows($result)>0){
			while($auteur=$db->next($result)){
				$auteurs[]=$auteur['auteur'];
			}
			sort($auteurs);
			return array_filter($auteurs);
		}else{
			return array();
		}
	}
}

?>
