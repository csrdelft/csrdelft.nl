<?php
/*
 * boek.class.php	| 	Gerrit Uitslag
 *
 * boeken
 *
 */
require_once 'rubriek.class.php';
require_once 'auteur.class.php';
require_once 'formulier.class.php';

class Boek{

	private $id=0;
	private $titel;
	private $auteur_id; //auteur_id of biebauteur.auteur
	private $categorie_id = 108;//categorie_id of concat van 3x biebcategorie.categorie )
	private $uitgavejaar;
	private $uitgeverij;
	private $paginas;
	private $taal='Nederlands';
	private $isbn;
	private $code;

	private $error;
	private $nieuwboekform;
	private $boekbeschrijvingform;
	private $beschrijving;
	private $beschrijvingsid;
	private $beschrijvingen = null;

	public function __construct($init){
		$this->load($init);
	}

	public function load($init=0){
		if(is_array($init)){
			$this->array2properties($init,$lookup=false);
		}else{
			$this->id=(int)$init;
			if($this->getID()==0){
				//Bij $this->id==0 gaat het om een nieuw boek. Hier
				//zetten we de defaultwaarden voor het nieuwe boek.
				//$this->setPropss(..);
				$this->assignFieldsNieuwboekForm();
			}else{
				$db=MySql::instance();
				$query="
					SELECT id, titel, auteur_id, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code
					FROM biebboek
					WHERE ID=".$this->getID().";";
				$boek=$db->getRow($query);
				if(is_array($boek)){
					$this->array2properties($boek,$lookup=false);
					$this->assignFieldsBeschrijvingForm();
				}else{
					throw new Exception('load() mislukt. Bestaat het boek wel?');
				}
			}
		}

	}
	private function array2properties($properties,$lookup=true){
		foreach ($properties as $prop => $value){
			$this->setValue($prop, $value, $lookup);
		}
	}

	public function getID(){			return $this->id;}
	public function getTitel(){			return $this->titel;}
	public function getAuteurId(){		return $this->auteur_id;}
	public function getAuteur(){
		if(is_int($this->auteur_id)){
			$auteur = new Auteur($this->auteur_id);
			return $auteur->getNaam();
		}else{
			return $this->auteur_id;
		}
	}
	public function getRubriekId(){		return $this->categorie_id;}
	public function getRubriek(){
		if(is_int($this->categorie_id)){
			try{
				$rubriek = new Rubriek($this->categorie_id);
				return $rubriek->getRubrieken();
			}catch(Exception $e){
				return ''; // o.a. voor catid=0
			}
		}else{
			return $this->categorie_id;
		}
	}

	public function getUitgavejaar(){	return $this->uitgavejaar;}
	public function getUitgeverij(){	return $this->uitgeverij;}
	public function getPaginas(){		return $this->paginas;}
	public function getTaal(){			return $this->taal;}
	public function getISBN(){			return $this->isbn;}
	public function getCode($suggestie=false){
		if($suggestie AND $this->code=="" AND is_int($this->categorie_id)){
			$code = $this->categorie_id.'.';
			if($this->getAuteur()!=""){
				$code .= strtolower(substr($this->getAuteur(),0,3)).'X';
			}
			return $code;
		}
		return $this->code;
	}

//	public function getProperty($key){
//		$allowedkeys = array('id', 'titel', 'uitgavejaar', 'uitgeverij', 'paginas', 'taal', 'isbn', 'code');
//		if(in_array($key, $allowedkeys)){
//			return $this->$key;
//		}elseif($key=='categorie'){
//			//TODO
//		}elseif($key=='rubriek'){
//			//TODO
//		}
//		return null;
//	}
	public function getEigenaar(){
		return 'x204';
	}
	public function getError(){
		return $this->error;
	}

	public function isCSRboek(){return true;} //TODO

	public function setValue($key, $value, $lookup=true){
		switch ($key) {
			//integers
			case 'id':
			case 'uitgavejaar':
			case 'paginas':
			case 'auteur_id':
			case 'categorie_id':
				$this->$key=(int)trim($value);
				break;
			//strings
			case 'auteur':
				if($lookup){
					//object Auteur maken, als auteur niet bestaat wordt deze toegevoegd
					try{
						$auteur = new Auteur($value);
					}catch(Exception $e){
						throw new Exception($e->getMessage().' Boek::setValue "auteur"');
					}
					$this->auteur_id = $auteur->getId();
					break;
				}
			case 'categorie':
			case 'rubriek':
				if($lookup){
					//object Rubriek maken, als rubriek niet bestaat wordt deze toegevoegd
					try{
						$rubriek = new Rubriek($value);
					}catch(Exception $e){
						throw new Exception($e->getMessage().' Boek::setValue "categorie"');
					}
					$this->categorie_id = $rubriek->getId();
					break;
				}elseif($key!='rubriek'){
					$var = $key.'_id'; // bewaart in $key_id veld de string, niet het id
					$this->$var=trim($value);
				}
				break;
			case 'titel':
			case 'uitgeverij':
			case 'taal':
			case 'code':
			case 'isbn':
				$this->$key=trim($value);
				break;
			case 'beschrijving':
				$this->beschrijving=$value;
				break;
			case 'beschrijvingsid':
				$this->beschrijvingsid=$value;
				break;
			default:
				throw new Exception('Veld ['.$key.'] is niet toegestaan Boek::setValue()');
		}
	}

	public function magVerwijderen($beschrijvingid=null){
		$uid=LoginLid::instance()->getUid();
		if(Loginlid::instance()->hasPermission('P_BIEB_MOD','groep:BASFCie')){ return true;}
		if($uid=='x999'){ return false;}
		
		//of boekbeschrijving mag verwijderen
		if($beschrijvingid!==null){
			$aBeschrijving=$this->getBeschrijving($beschrijvingid);
			return $aBeschrijving['schrijver_uid'] ==$uid;
		}else{
			//geen rechten om aan te passen
			return false;
		}
	}
	public function magBewerken($beschrijvingid=null){
		$uid=LoginLid::instance()->getUid();

		//admin of nobodies
		if($this->magVerwijderen() OR Loginlid::instance()->hasPermission('P_BIEB_EDIT')){ return true;}
		if($uid=='x999'){ return false;}

		//of boekbeschrijving mag aanpassen
		if($beschrijvingid!==null){
			$aBeschrijving=$this->getBeschrijving($beschrijvingid);
			return $aBeschrijving['schrijver_uid'] ==$uid;
		}elseif($this->isEigenaar()){
			//is eigenaar van boek
			return true;
		}else{
			//geen rechten om aan te passen
			return false;
		}
	}
	public function magBekijken(){
		return Loginlid::instance()->hasPermission('P_BIEB_READ') OR $this->magBewerken();
	}
	public function isEigenaar(){
		return Loginlid::instance()->hasPermission($this->getEigenaar());
	}

	/*
	 * Slaat het object Boek op
	 */
	public function save(){
		$db=MySql::instance();
		$qSave="
			INSERT INTO biebboek (
				titel, auteur_id, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code
			) VALUES (
				'".$db->escape($this->getTitel())."',
				'".(int)$this->getAuteurId()."',
				'".(int)$this->getRubriekId()."',
				'".(int)$this->getUitgavejaar()."',
				'".$db->escape($this->getUitgeverij())."',
				'".(int)$this->getPaginas()."',
				'".$db->escape($this->getTaal())."',
				'".$db->escape($this->getISBN())."',
				'".$db->escape($this->getCode())."'
			);";
		if($db->query($qSave)){
			//id ook opslaan in object Boek.
			$this->id=$db->insert_id();
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::save()';
		return false;
	}


	/*
	 * Opslaan van waarde van een bewerkbaar veld 
	 */
	public function saveProperty($key, $value){
		$allowedkeys = array('titel', 'auteur_id', 'categorie_id', 'uitgavejaar', 'uitgeverij', 'paginas', 'taal', 'isbn', 'code');
		if(in_array($key, $allowedkeys)){
			$db=MySql::instance();
			if(in_array(array('auteur_id', 'categorie_id', 'uitgavejaar', 'paginas'))){
				$value=(int)$value;
			}else{
				$value=$db->escape($value);
			}
			$qSave="
				UPDATE biebboek SET
					.$key.= '".$value."',
				WHERE id= ".$this->getId()."
				LIMIT 1;";
			if($db->query($qSave)){
				return true;
			}
			$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::saveProperty()';
			return false;
		}
		return false;
	}
	/*
	 * Verwijder een boek
	 */
	public function delete(){
		if($this->getId()==0){
			$this->error.='Kan geen lege boek met id=0 wegkekken. Boek::delete()';
			return false;
		}
		$db=MySql::instance();
		$qDeleteBeschrijvingen="DELETE FROM biebbeschrijving WHERE boek_id=".$this->getId().";";
		$qDeleteExemplaren="DELETE FROM biebexemplaar WHERE boek_id=".$this->getId()." LIMIT 1;";
		$qDeleteBoek="DELETE FROM biebboek WHERE id=".$this->getId()." LIMIT 1;";
		return $db->query($qDeleteBeschrijvingen) AND $db->query($qDeleteExemplaren) AND $db->query($qDeleteBoek);
	}


	/*****************************************
	 * Boekrecensies/beschrijvingen
	 * 
	 * laad beschrijvingen van dit boek
	 */
	public function loadBeschrijvingen(){
		$db=MySql::instance();
		$query="
			SELECT id, schrijver_uid, beschrijving, toegevoegd, bewerkdatum
			FROM biebbeschrijving
			WHERE boek_id=".(int)$this->getId()."
			ORDER BY toegevoegd;";
		$result=$db->query($query);
		echo mysql_error();
		if($db->numRows($result)>0){
			while($beschrijving=$db->next($result)){
				$this->beschrijvingen[]=$beschrijving;
			}
		}else{
			return false;
		}
		return $db->numRows($result);
	}

	/*
	 * Geeft beschrijvingen van dit boek
	 */
	public function getBeschrijvingen(){
		if($this->beschrijvingen===null){
			$this->loadBeschrijvingen();
		}
		return $this->beschrijvingen; 
	}
	/*
	 * Aantal beschrijvingen
	 */
	public function countBeschrijvingen(){
		if($this->beschrijvingen===null){
			$this->loadBeschrijvingen();
		}
		return count($this->beschrijvingen);
	}
	/*
	 * Geeft array terug voor een $beschrijvingsid, anders de tekst van beschrijving die in Boek is opgeslagen.
	 */
	public function getBeschrijving($beschrijvingid=null){
		if($beschrijvingid===null){
			return $this->beschrijving;
		}else{
			$db=MySql::instance();
			$query="
				SELECT beschrijving
				FROM biebbeschrijving
				WHERE id=".(int)$beschrijvingid."
				LIMIT 1;";
			$result=$db->query($query);
			echo mysql_error();
			if($db->numRows($result)>0){
				$beschrijving = $db->next($result);
				return $beschrijving['beschrijving'];
			}else{
				return 'Mislukt. Boek::getBeschrijving()';
			}
		}
	}
	/*
	 * Geeft $beschrijvingsid
	 */
	public function getBeschrijvingsId(){
		return $this->beschrijvingsid;
	}

	/*
	 * Sla boekrecensie/beschrijving op
	 */
	public function saveBeschrijving($bewerken=false){
		$db=MySql::instance();
		if($bewerken==false){
			$qSave="
				INSERT INTO biebbeschrijving (
					boek_id, schrijver_uid, beschrijving, toegevoegd
				) VALUES (
					'".(int)$this->getID()."',
					'".$db->escape(Loginlid::instance()->getUid())."',
					'".$db->escape($this->getBeschrijving())."',
					'".getDateTime()."'
				);";
		}else{
			$qSave="
				UPDATE biebbeschrijving SET
					beschrijving= '".$db->escape($this->getBeschrijving())."',
					bewerkdatum='".getDateTime()."'
				WHERE id= ".$this->getBeschrijvingsId()."
				LIMIT 1;";
		}
		if($db->query($qSave)){
			$this->beschrijvingid=$db->insert_id();//id van beschrijving weer tijdelijk opslaan, zodat we beschrijving kunnen linken
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::saveBeschrijving()';
		return false;
	}

	/*
	 * verwijder beschrijving
	 */
	public function verwijderBeschrijving($beschrijvingsid){
		if($beschrijvingsid==0){
			$this->error.='Beschrijving 0 bestaat niet. Boek::verwijderBeschrijving()';
			return false;
		}
		$db=MySql::instance();
	 	$qVerwijderBeschrijving="DELETE FROM biebbeschrijving WHERE id=".(int)$beschrijvingsid." LIMIT 1;";
		return $db->query($qVerwijderBeschrijving);
	}

	/******************************************
	 * methodes voor een nieuw boek formulier *
	 ******************************************/

	/*
	 * Definiëren van de velden van het nieuw boek formulier
	 * Als we ze hier toevoegen, dan verschijnen ze ook automagisch in het boekaddding,
	 * en ze worden gecontroleerd met de eigen valideerfuncties.
	 */
	public function assignFieldsNieuwboekForm(){
		//Iedereen die bieb mag bekijken mag nieuwe boeken toevoegen
		if($this->magBekijken()){
			$nieuwboekform[]=new Comment('Boekgegevens:');
			$nieuwboekform[]=new RequiredBiebSuggestInputField('titel', $this->getTitel(), 'Titel', 200,Catalogus::getAllValuesOfProperty('titel'));
			$nieuwboekform[]=new SuggestInputField('auteur', $this->getAuteurId(),'Auteur',100, Auteur::getAllAuteurs($short=true));
			$nieuwboekform[]=new IntField('paginas', $this->getPaginas() , "Pagina's", 10000, 0);
			$nieuwboekform[]=new SuggestInputField('taal', $this->getTaal(), 'Taal', 25, Catalogus::getAllValuesOfProperty('taal'));
			$nieuwboekform[]=new BiebSuggestInputField('isbn', $this->getISBN(), 'ISBN-nummer',15, Catalogus::getAllValuesOfProperty('isbn'));
			$nieuwboekform[]=new SuggestInputField('uitgeverij', $this->getUitgeverij(), 'Uitgeverij', 100, Catalogus::getAllValuesOfProperty('uitgeverij'));
			$nieuwboekform[]=new IntField('uitgavejaar', $this->getUitgavejaar(), 'Uitgavejaar',2100,0);
			$nieuwboekform[]=new SelectField('rubriek', $this->getRubriekId(), 'Rubriek',Rubriek::getAllRubrieken($samenvoegen=true,$short=true));
			$nieuwboekform[]=new CodeField('code', $this->getCode(true), 'Biebcode');

			$this->nieuwboekform=$nieuwboekform;
		}
	}
	public function assignFieldsBeschrijvingForm(){
		if($this->magBekijken()){
			$boekbeschrijvingform[]=new Comment('Geef uw beschrijving of recensie van het boek:');
			$boekbeschrijvingform[]=new RequiredPreviewTextField('beschrijving', $this->getBeschrijving(), '.');

			$this->boekbeschrijvingform=$boekbeschrijvingform;
		}
	}
	public function setCommentBeschrijvingForm($tekst){
		$this->boekbeschrijvingform['0'] = new Comment($tekst);
	}
	/*
	 * Geeft objecten van het nieuw boek formulier terug
	 */
	public function getFields($form){ 
		switch($form){
			case 'nieuwboek':
				return $this->nieuwboekform;
				break;
			case 'beschrijving':
				return $this->boekbeschrijvingform;
				break;
		}
		return null;
	}
	
	/*
	 * Controleren of de velden van nieuw boek formulier zijn gePOST
	 */
	public function isPostedFields($form){
		$posted=false;
		foreach($this->getFields($form) as $field){
			if($field instanceof FormField AND $field->isPosted()){
				$posted=true;
			}
		}
		return $posted;
	}
	/*
	 * Controleren of de velden van nieuw boek formulier correct zijn
	 */
	public function validFields($form){
		//alle veldjes langslopen, en kijken of ze valideren.
		$valid=true;
		foreach($this->getFields($form) as $field){
			//we checken alleen de formfields, niet de comments enzo.
			if($field instanceof FormField AND !$field->valid()){
				$valid=false;
			}
		}
		return $valid;
	}
	/*
	 * Slaat de velden van nieuw boek formulier op
	 */
	public function saveFields($form,$bewerken=false){
		//object Boek vullen
		foreach($this->getFields($form) as $field){
			if($field instanceof FormField){
				$this->setValue($field->getName(), $field->getValue());
			}
		}
		//object Boek opslaan
		if($form=='nieuwboek'){
			if($this->save()){
				return true;
			}
		}elseif($form=='beschrijving'){
			if($this->saveBeschrijving($bewerken)){
				return true;
			}
		}
		return false;
	}

	/***************************************
	 * methodes voor een javascript formulier *
	 ***************************************/

	public function validField($id,$waarde){
		switch ($key) {
			//integers
			case 'uitgavejaar':
			case 'paginas':
			//objecten
			case 'auteur':
			case 'categorie':
			//strings
			case 'titel':
				$this->isMaxlen($waarde,200);
				break;
			case 'uitgeverij':
				$this->isMaxlen($waarde,100);
				break;
			case 'taal':
				$this->isMaxlen($waarde,25);
				break;
			case 'isbn':
				$this->isMaxlen($waarde,15);
				break;
			case 'code':
				$this->isMaxlen($waarde,10);
				break;
			default:
				throw new Exception('Veld ['.$key.'] is niet toegestaan Boek::saveField()');
		}
	}
	private function isMaxlen($value,$max){
		if(mb_strlen($value)>$max){
			$this->error='Maximaal '.$max.' karakters toegestaan.';
			return false;
		}
		return true;
	}

	public function saveField($id,$waarde){
		switch ($key) {
			//integers
			case 'uitgavejaar':
			case 'paginas':
			//objecten
			case 'auteur':
			case 'categorie':
			//strings
			case 'titel':
				if(mb_strlen($this->getValue())>$this->max_len){
					$this->error='Maximaal '.$this->max_len.' karakters toegestaan.';
					return false;
				}
			case 'uitgeverij':
			case 'taal':
			case 'code':
			case 'isbn':
		
				break;
			default:
				throw new Exception('Veld ['.$key.'] is niet toegestaan Boek::saveField()');
		}
		$query = "
		UPDATE biebboek SET
			op= '".$op."',
			functie= '".$db->escape($functie)."',
			prioriteit= ".$prioriteit.",
			moment='".getDateTime()."'
		WHERE id= ".$this->getId()." AND uid= '".$uid."'
		LIMIT 1;";
	}

	public function assignAjaxFieldsForm(){
		//Iedereen die bieb mag bekijken mag nieuwe boeken toevoegen
		if($this->magBewerken){
			$form[]=new RequiredInputAjaxField('titel', $this->getTitel(), 'Titel', 200);
			$form[]=new IntAjaxField('paginas', $this->getPaginas() , "Pagina's", 10000, 0);
			$form[]=new SuggestInputASField('taal', $this->getTaal(), 'Taal', 25, Catalogus::getTalen());
			$form[]=new InputAjaxField('isbn', $this->getISBN(), 'ISBN-nummer',15);
			$form[]=new InputAjaxField('uitgeverij', $this->getUitgeverij(), 'Uitgeverij',100);
			$form[]=new IntAjaxField('uitgavejaar', $this->getUitgavejaar(), 'Uitgavejaar',4);
			$form[]=new SelectAjaxField('rubriek', $this->getRubriek(), 'Rubriek',Rubriek::getAllRubrieken($samenvoegen=true));
			$form[]=new InputAjaxField('code', '000.ach', 'Biebcode',10);
		}
		$this->form=$form;
	}

	public function assignOpmerkingFieldsForm(){
		
	}
}
?>
