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
	
	private $valid=true;
	private $errors='';
	
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
			$this->content->invokeRefresh('We geven geen 0-groepen weer! (Groepcontroller::__construct())', CSR_ROOT.'groepen/');
		}
		//Normale gebruikers mogen niet alle acties doen.
		$allow=array('default', 'aanmelden');
		if(!in_array($this->action, $allow) AND !$this->groep->magBewerken()){
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
		//Velden beschikbaar voor groepadmins.
		if($this->groep->isAdmin()){
			//snaam is alleen relevant bij het maken van een nieuwe groep
			if($this->groep->getId()==0 AND !isset($_POST['snaam'])){
				$this->addError("Korte naam is verplicht bij een nieuwe groep.");
			}else{
				if($this->groep->getId()==0){
					if(strlen(trim($_POST['snaam']))<3){
						$this->addError("Korte naam moet minstens drie tekens lang zijn.");
					}
					if(strlen(trim($_POST['snaam']))>20){
						$this->addError("Korte naam mag maximaal 20 tekens bevatten.");
					}
					if(preg_match('/\s/', trim($_POST['snaam']))){
						$this->addError("Korte naam mag geen spaties bevatten.");
					}
				}
			}
			
			if(isset($_POST['naam'], $_POST['sbeschrijving'], $_POST['status'], $_POST['begin'], $_POST['einde'], $_POST['toonFuncties'])){
				if(strlen(trim($_POST['naam']))<3){
					$this->addError("Naam moet minstens drie tekens lang zijn.");
				}
				if(strlen(trim($_POST['sbeschrijving']))<5){
					$this->addError("Korte beschrijving moet minstens vijf tekens lang zijn.");
				}
				if(!preg_match('/\d{4}-\d{2}-\d{2}/', trim($_POST['begin']))){
					$this->addError("De begindatum is niet geldig. Gebruik JJJJ-mm-dd.");
				}
				if(trim($_POST['begin'])=='0000-00-00'){
					$this->addError("De begindatum mag niet 0000-00-00 zijn.");
				}
				
				if(!preg_match('/\d{4}-\d{2}-\d{2}/', trim($_POST['einde']))){
					$this->addError("De begindatum is niet geldig. Gebruik JJJJ-mm-dd.");
				}
				if(!in_array($_POST['toonFuncties'], array('tonen', 'verbergen', 'niet'))){
					$this->addError("ToonFuncties mag deze waarden niet hebben.");
				}
				
				if(!preg_match('/(h|f|o)t/', $_POST['status'])){
					$this->addError("De status is niet geldig.");
				}else{
					if($_POST['status']=='ot' AND trim($_POST['einde'])=='0000-00-00'){
						$this->addError("Een o.t. groep moet een einddatum bevatten.");
					}
					
					//Controleren of er geen h.t. groep bestaat met dezelfde snaam.
					if($this->groep->getId()==0 AND isset($_POST['snaam'])){
						$snaam=$_POST['snaam'];
					}else{
						$snaam=null;
					}
					if($_POST['status']=='ht'){
						if($this->groep->hasHt($snaam)){
							$this->addError("Er is al een h.t.-groep voor deze soort, kies een andere status.");
						}
						if(isset($_POST['aanmeldbaar'], $_POST['limiet'])){
							if($_POST['limiet']<0 OR $_POST['limiet']>200){
								$this->addError("Kies een limiet tussen 0 en 200");
							}
						}
					}
				}
			}else{
				$this->addError("Het formulier is niet compleet.");
			}
			
		}
		//velden beschikbaar voor groepOps
		if(!isset($_POST['beschrijving'])){
			$this->addError("Het veld beschrijving mist.");
		}
		return $this->valid;
	}
	public function addError($error){
		$this->valid=false;
		$this->errors.=$error.'<br />';
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
			$this->groep->setValue('status', 'ot');
			$this->groep->setValue('snaam', $this->queryparts[2]);
		}
		
		if($this->isPOSTed()){
			//validatie moet nog even gemaakt worden. TODO dus nog.
			if($this->groepValidator()){
				//slaan we een nieuwe groep op?
				if($this->groep->getId()==0 ){
					$this->groep->setValue('snaam',$_POST['snaam']);
				}
				
				//velden alleen voor admins
				if($this->groep->isAdmin()){
					$this->groep->setValue('naam', $_POST['naam']);
					$this->groep->setValue('sbeschrijving', $_POST['sbeschrijving']);
					$this->groep->setValue('begin', $_POST['begin']);
					$this->groep->setValue('einde', $_POST['einde']);
					$this->groep->setValue('status', $_POST['status']);
					
					//ht-groepen kunnen aanmeldbaar gemaakt worden, ot groepen zijn nooit
					//aanmeldbaar
					if($this->groep->getStatus()=='ht'){
						if(isset($_POST['aanmeldbaar'])){
							$this->groep->setValue('aanmeldbaar', 1);
							$this->groep->setValue('limiet', $_POST['limiet']);	
						}else{
							$this->groep->setValue('aanmeldbaar', 0);
							$this->groep->setValue('limiet', 0);	
						}
					}else{
						$this->groep->setValue('aanmeldbaar', 0);
					}
					$this->groep->setValue('toonFuncties', $_POST['toonFuncties']);
				}
				$this->groep->setValue('beschrijving', $_POST['beschrijving']);
				
				if($this->groep->save()){
					$melding='Opslaan van groep gelukt!';	
				}else{
					$melding='Opslaan van groep mislukt. (returned from Groep::save() called by Groepcontroller::action_bewerken())';
				}
				$this->content->invokeRefresh($melding, $this->getUrl('default'));
			}else{
				//geposte waarden in het object stoppen zodat de template ze zo in het 
				//formulier kan knallen
				$fields=array('snaam', 'naam', 'sbeschrijving', 'beschrijving', 'zichtbaar', 'status', 'begin', 'einde', 'aanmeldbaar', 'limiet');
				
				foreach($fields as $field){
					if(isset($_POST[$field])){
						$this->groep->setValue($field, $_POST[$field]);
					}
				}
				//de eventuele fouten van de groepValidator aan de melding toevoegen.
				$this->content->setMelding($this->errors);

			}
		}
	}
	/*
	 * een groep verwijderen.
	 */
	public function action_verwijderen(){
		if($this->groep->delete()){
			$melding='Groep met succes verwijderd.';
		}else{
			$melding='Groep verwijderen mislukt Groepcontroller::action_deleteGroep()';
		}
		$this->content->invokeRefresh($melding, CSR_ROOT.'groepen/');
	}
	
	/*
	 * Ingelogde leden kunnen zich aanmelden.
	 */
	public function action_aanmelden(){
		if($this->groep->magAanmelden()){
			if($this->groep->meldAan()){
				$melding='';
			}else{
				$melding='Aanmelden voor groep mislukt.';
			}
			
		}else{
			$melding='U kunt zich niet aanmelden voor deze groep, wellicht is hij vol.';
		}
		$this->content->invokeRefresh($melding, $this->getUrl('default'));
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
