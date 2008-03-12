<?php
/*
 * class.groepcontroller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * Groepcontroller wordt ge__construct() met één argument, een querystring.
 * Die bestaat uit door slashes gescheiden waarden in de volgende volgorde:
 * 
 * <groepId of groepNaam>/[<actie>/[<parameters voor actie>]]
 * 
 * bijvoorbeeld voor het verwijderen van een lid uit de PubCie
 * 
 * PubCie/verwijderLid/0436
 * 
 * Het gaat hierbij om GET-parameters, POST-dingen worden gewoon in de 
 * controller uit de POST-array getrokken... 
 */
require_once('class.groepen.php');
require_once('class.controller.php');

class Groepcontroller extends Controller{
	
	private $groep;
	private $queryparts=array();
	private $lid;
	
	private $errors;
	
	public function __construct($querystring){
		$this->lid=Lid::get_Lid();
		$this->queryparts=explode('/', $querystring);
		
		//groep-object inladen
		if(isset($this->queryparts[0])){
			$this->groep=new Groep($this->queryparts[0]);
		}
		//action voor deze controller goedzetten.
		if(isset($this->queryparts[1]) AND $this->hasAction($this->queryparts[1])){
			$this->action=$this->queryparts[1];
		}
		//content-object aanmaken..
		$this->content=new Groepcontent($this->groep);
		
		//controleer dat we geen lege groep weergeven.
		if($this->action=='default' AND $this->groep->getId()==0){
			$this->content->invokeRefresh('We geven geen 0-groepen weer! (Groepcontroller::__construct())', CSR_ROOT.'/groepen/');
		}
		//Normale gebruikers mogen enkel default-acties doen.
		if(!$this->groep->magBewerken()){
			$this->action='default';
		}
		$this->performAction();
	}

	public function action_default(){
		$this->content->setAction('view');
	}
	public function getUrl($action=null){
		$url=CSR_ROOT.'groepen/'.$this->groep->getType().'/'.$this->groep->getId().'/';
		if($action!=null AND $this->hasAction($action)){
			if($action!='default'){
				$url.=$action;
			}
		}elseif($this->action!='default'){
			$url.=$this->action;
		}
		return $url;
	}
	
	
	/*
	 * Valideer de formulierinvoer voor een groep.
	 * Beetje gecompliceerd door de verschillende permissielagen, maargoed.
	 */
	public function groepValidator(){
		$valid=true;
		//Velden beschikbaar voor groepadmins.
		if($this->groep->isAdmin()){
			//snaam is alleen relevant bij het maken van een nieuwe groep
			if($this->groep->getId()==0 AND !isset($_POST['snaam'])){
				$valid=false;
				$this->errors.="Korte naam is verplicht bij een nieuwe groep.<br />";
			}else{
				if($this->groep->getId()==0){
					if( strlen(trim($_POST['snaam']))<3){
						$valid=false;
						$this->errors.="Korte naam moet minstens drie tekens lang zijn.<br />";
					}
					if(strlen(trim($_POST['snaam']))>20){
						$valid=false;
						$this->errors.="Korte naam mag maximaal 20 tekens bevatten.<br />";
					}
					if(preg_match('/\s/', trim($_POST['snaam']))){
						$valid=false;
						$this->errors.="Korte naam mag geen spaties bevatten.<br />";
					}
				}
			}
			
			if(isset($_POST['naam'], $_POST['sbeschrijving'], $_POST['status'], $_POST['installatie'])){
				if(strlen(trim($_POST['naam']))<5){
					$valid=false;
					$this->errors.="Naam moet minstens vijf tekens lang zijn.<br />";
				}
				if(strlen(trim($_POST['sbeschrijving']))<5){
					$valid=false;
					$this->errors.="Korte beschrijving moet minstens vijf tekens lang zijn.<br />";
				}
				if(!preg_match('/\d{4}-\d{2}-\d{2}/', trim($_POST['installatie']))){
					$valid=false;
					$this->errors.="De installatiedatum is niet geldig. Gebruik JJJJ-mm-dd.<br />";
				}
				if(trim($_POST['installatie'])=='0000-00-00'){
					$valid=false;
					$this->errors.="De installatiedatum mag niet 0000-00-00 zijn.<br />";
				}
				if(!preg_match('/(h|f|o)t/', $_POST['status'])){
					$valid=false;
					$this->errors.="De status is niet geldig.<br />";
				}else{
					//Controleren of er geen h.t. groep bestaat met dezelfde snaam.
					if($this->groep->getId()==0 AND isset($_POST['snaam'])){
						$snaam=$_POST['snaam'];
					}else{
						$snaam=null;
					}
					if($_POST['status']=='ht' AND $this->groep->hasHt($snaam)){
						$valid=false;
						$this->errors.="Er is al een h.t.-groep voor deze soort, kies een andere status.<br />";
					}
				}
			}else{
				$valid=false;
				$this->errors.="Het formulier is niet compleet.<br />";
			}
			
		}
		//velden beschikbaar voor groepOps
		if(!isset($_POST['beschrijving'])){
			$valid=false;
			$this->errors.="Het veld beschrijving mist.<br />";
		}
		return $valid;
	}
	/*
	 * Bewerken en opslaan van groepen. Groepen mogen door groepadmins (groeplid.op=='1')
	 * voor een deel bewerkt worden, de P_ADMINS kunnen alles aanpassen. Hier wordt de
	 * toegangscontrole voor verschillende velden geregeld.
	 */
	public function action_bewerken(){
		$this->content->setAction('edit');

		//Als er een derde argument meegegeven wordt is dat een korte naam
		//die we invullen in het formulier.
		if(isset($this->queryparts[2]) AND preg_match('/\w{3,20}/', $this->queryparts[2])){
			$this->groep->setStatus('ot');
			$this->groep->setSnaam($this->queryparts[2]);
		}
		
		if($this->isPOSTed()){
			//validatie moet nog even gemaakt worden. TODO dus nog.
			if($this->groepValidator()){
				//slaan we een nieuwe groep op?
				if($this->groep->getId()==0 ){
					$this->groep->setSnaam($_POST['snaam']);
				}
				
				//velden alleen voor admins
				if($this->groep->isAdmin()){
					$this->groep->setNaam($_POST['naam']);
					$this->groep->setSbeschrijving($_POST['sbeschrijving']);
					$this->groep->setInstallatie($_POST['installatie']);
					$this->groep->setStatus($_POST['status']);
				}
				$this->groep->setBeschrijving($_POST['beschrijving']);
				
				if($this->groep->save()){
					$melding='Opslaan van groep gelukt!';	
				}else{
					$melding='Opslaan van groep mislukt. (Groep::save() called by Groepcontroller::action_bewerken())';
				}
				$this->content->invokeRefresh($melding, $this->getUrl('default'));
			}else{
				//geposte waarden in het object stoppen zodat de template ze zo in het 
				//formulier kan knallen
				if(isset($_POST['snaam'])){			$this->groep->setSnaam($_POST['snaam']); }
				if(isset($_POST['naam'])){			$this->groep->setNaam($_POST['naam']); }
				if(isset($_POST['sbeschrijving'])){	$this->groep->setSbeschrijving($_POST['sbeschrijving']); }
				if(isset($_POST['beschrijving'])){	$this->groep->setBeschrijving($_POST['beschrijving']); }
				if(isset($_POST['installatie'])){	$this->groep->setInstallatie($_POST['installatie']); }
				if(isset($_POST['status'])){		$this->groep->setStatus($_POST['status']); }
				//de eventuele fouten van de groepValidator aan de melding toevoegen.
				$this->content->setMelding($this->errors);

			}
		}
	}
	
	/*
	 * Leden toevoegen aan een groep.
	 */
	public function action_addLid(){
		$this->content->setAction('addLid');
		if(isset($_POST['naam'], $_POST['functie']) AND is_array($_POST['naam']) AND is_array($_POST['functie']) AND count($_POST['naam'])==count($_POST['functie'])){
			//nieuwe commissieleden erin stoppen.
			$success=true;
			for($i=0; $i<count($_POST['naam']); $i++){
				if($this->lid->isValidUid($_POST['naam'][$i])){
					if(!$this->groep->addLid($_POST['naam'][$i], $_POST['functie'][$i])){
						//er gaat iets mis, zet $success op false;	
						$success=false;
					}
				}
			}
			if($success===true){
				$melding='Leden met succes toegevoegd.';
			}else{
				$melding='Niet alle leden met succes toegevoegd. Wellicht waren sommigen al lid van deze groep? (Groepcontroller::action_addLid())';
			}
			$this->content->invokeRefresh($melding, $this->getUrl('default'));
		}
		
	}
	public function action_verwijderLid(){
		if(isset($this->queryparts[2]) AND $this->lid->isValidUid($this->queryparts[2]) AND $this->groep->magBewerken()){
			if($this->groep->verwijderLid($this->queryparts[2])){
				$melding='Lid is uit groep verwijderd.';
			}else{
				$melding='Lid uit groep verwijderen mislukt.';
			}
			$this->content->invokeRefresh($melding, $this->getUrl('default'));
		}	
	}
	
	public function action_geschiedenis(){
		$this->content=new Groepgeschiedeniscontent(new Groepen($_GET['gtype']));
	}
	
}
?>
