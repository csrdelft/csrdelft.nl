<?php
/*
 * boek.class.php	| 	Gerrit Uitslag
 *
 * boeken
 *
 */
require_once 'rubriek.class.php';
require_once 'auteur.class.php';

class Boek{

	private $id=0;
	private $titel;
	private $auteur_id; //auteur_id of biebauteur.auteur
	private $categorie_id;//categorie_id of concat van 3x biebcategorie.categorie )
	private $uitgavejaar;
	private $uitgeverij;
	private $paginas;
	private $taal='Nederlands';
	private $isbn;
	private $code='000.ach';

	private $error;
	private $form;

	public function __construct($init){
		$this->load($init);
	}

	public function load($init=0){
		if(is_array($init)){
			$this->array2properties($init);
		}else{
			$this->id=(int)$init;
			if($this->getID()==0){
				//Bij $this->id==0 gaat het om een nieuw boek. Hier
				//zetten we de defaultwaarden voor het nieuwe boek.
				//$this->setPropss(..);
			}else{
				$db=MySql::instance();
				$query="
					SELECT id, titel, auteur_id, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code
					FROM biebboek
					WHERE ID=".$this->getID().";";
				$boek=$db->getRow($query);
				if(is_array($boek)){
					$this->array2properties($boek);
				}else{
					throw new Exception('load() mislukt. Bestaat het boek wel?');
				}
			}
		}

	}
	private function array2properties($properties){
		foreach ($properties as $prop => $value){
			$this->setValue($prop, $value);
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
			$rubriek = new Rubriek($this->categorie_id);
			return $rubriek->getRubrieken();
		}else{
			return $this->categorie_id;
		}
	}

	public function getUitgavejaar(){	return $this->uitgavejaar;}
	public function getUitgeverij(){	return $this->uitgeverij;}
	public function getPaginas(){		return $this->paginas;}
	public function getTaal(){			return $this->taal;}
	public function getISBN(){			return $this->isbn;}
	public function getCode(){			return $this->code;}

	public function isCSRboek(){return true;} //TODO

	public function setValue($key, $value){
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
			case 'categorie':
				$var = $key.'_id'; // bewaart in $key_id veld de string, niet het id
				$this->$var=trim($value);
				break;
			case 'titel':
			case 'uitgeverij':
			case 'taal':
			case 'code':
			case 'isbn':
				$this->$key=trim($value);
				break;
			default:
				throw new Exception('Veld ['.$key.'] is niet toegestaan Boek::setValue()');
		}
	}

	public function magVerwijderen(){
		return Loginlid::instance()->hasPermission('P_BIEB_MOD','groep:BASFCie');
	}
	public function magBewerken(){
		return $this->magVerwijderen() OR Loginlid::instance()->hasPermission('P_BIEB_EDIT');
	}

	public function save(){
		$db=MySql::instance();
		$qSave="
			INSERT INTO groep (
				titel, auteur_id, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code
			) VALUES (
				'".$db->escape($this->getSnaam())."',
				'".$db->escape($this->getNaam())."',
				'".$db->escape($this->getSbeschrijving())."',
				'".$db->escape($this->getBeschrijving())."',
				".$this->getTypeId().",
				'".$db->escape($this->getZichtbaar())."',
				'".$db->escape($this->getStatus())."',
				'".$db->escape($this->getBegin())."',
				'".$db->escape($this->getEinde())."',
				'".$db->escape($this->getAanmeldbaar())."',
				".(int)$this->getLimiet().",
				'".$this->getToonFuncties()."',
				'".$db->escape($this->getFunctiefilter())."',
				'".$this->getToonPasfotos()."',
				'".$this->getLidIsMod()."',
				'".$db->escape($this->getEigenaar())."'
			);";
		if($db->query($qSave)){
			//id ook opslaan.
			$this->id=$db->insert_id();
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Groep::save()';
		return false;
	}
	public function saveProperty($key, $value){
		
					"
					UPDATE biebboek SET
						op= '".$op."',
						functie= '".$db->escape($functie)."',
						prioriteit= ".$prioriteit.",
						moment='".getDateTime()."'
					WHERE groepid= ".$this->getId()." AND uid= '".$uid."'
					LIMIT 1;";
	}


	/*
	 * een addboek formulier
	 */
	public function isPosted(){
		$posted=false;
		foreach($this->form as $field){
			if($field instanceof FormField AND $field->isPosted()){
				$posted=true;
			}
		}
		return $posted;
	}
	public function valid(){
		//alle veldjes langslopen, en kijken of ze valideren.
		$valid=true;
		foreach($this->form as $field){
			//we checken alleen de formfields, niet de comments enzo.
			if($field instanceof FormField AND !$field->valid($this->getLid())){
				$valid=false;
			}
		}
		return $valid;
	}
	public function getFields(){ return $this->form; }

	/*
	 * Alle boekvelden die ingevuld kunnen worden hier definiëren.
	 * Als we ze hier toevoegen, dan verschijnen ze ook automagisch in het boekaddding,
	 * en ze worden gecontroleerd met de eigen valideerfuncties.
	 */
	public function assignFields(){
		LidCache::updateLid($this->lid->getUid());

		//Iedereen die bieb mag bekijken mag nieuwe boeken toevoegen
		if(LoginLid::instance()->hasPermission('P_BIEB_READ')){
			$form[]=new Comment('Boekgegevens:');
			$form[]=new RequiredInputField('titel', $this->getTitel(), 'Titel', 200);
			$form[]=new IntField('paginas', $this->getPaginas() , "Pagina's", 10000, 0);
			$form[]=new SuggestInputField('taal', $this->getTaal(), 'Taal', 10, Catalogus::getTalen());
			$form[]=new InputField('isbn', $this->getISBN(), 'ISBN-nummer',15);
			$form[]=new InputField('uitgeverij', $this->getUitgeverij(), 'Uitgeverij',100);
			$form[]=new IntField('uitgavejaar', $this->getUitgavejaar(), 'Uitgavejaar',4);
			$form[]=new SelectField('rubriek', $this->getRubriek(), 'Rubriek',Rubriek::getAllRubrieken($samenvoegen=true));
			$form[]=new Comment('alleen voor C.S.R.-bibliotheek:');
			$form[]=new InputField('code', '000.ach', 'Biebcode',10);
		}
		$this->form=$form;
	}
}

?>
