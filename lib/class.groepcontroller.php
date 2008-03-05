<?php
/*
 * class.groepcontroller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */

class Controller{
	
	protected $action='default';
	protected $content=null;
	
	public function __construct(){
		
	}
	public function getContent(){
		return $this->content;
	}
	public function hasAction($action){
		return method_exists($this, 'action_'.$action);
	}
	
	protected function isPOSTed(){
		return $_SERVER['REQUEST_METHOD']=='POST';
	}
	//call the action
	protected function performAction(){
		$action='action_'.$this->action;
		if($this->hasAction($this->action)){
			$this->$action();
		}else{
			throw new Exception('Action undefined');
		}
	}
	protected function action_default(){
		return true;
	}
}

/*
 * Groepcontroller wordt ge__construct() met één argument, een querystring.
 * Die bestaat uit door slashes gescheiden waarden in de volgende volgorde:
 * 
 * <groepId of groepNaam>/[<actie>/[<parameters voor actie>]]
 * 
 */
class Groepcontroller extends Controller{
	
	private $groep;
	private $queryparts=array();
	private $lid;
	
	public function __construct($querystring){
		$this->lid=Lid::get_Lid();
		$this->queryparts=explode('/', $querystring);
		
		if(isset($this->queryparts[0])){
			$this->groep=new Groep($this->queryparts[0]);
		}
		if(isset($this->queryparts[1]) AND $this->hasAction($this->queryparts[1])){
			$this->action=$this->queryparts[1];
		}
		$this->content=new Groepcontent($this->groep);
		
		if(!$this->groep->magBewerken()){
			$this->action='default';
		}
		$this->performAction();
	}

	public function action_default(){
		$this->content->setAction('view');
	}
	public function getUrl($action=null){
		$url=CSR_ROOT.'groepen/groep/'.$this->groep->getId().'/';
		if($action!=null AND $this->hasAction($action) AND $action!='default'){
			$url.=$action;
		}elseif($this->action!='default'){
			$url.=$this->action;
		}
		return $url;
	}
	/*
	 * Bewerken en opslaan van groepen. Groepen mogen door groepadmins (groeplid.op=='1')
	 * voor een deel bewerkt worden, de P_ADMINS kunnen alles aanpassen. Hier wordt de
	 * toegangscontrole voor verschillende velden geregeld.
	 */
	public function action_bewerken(){
		$this->content->setAction('edit');
		
		if($this->isPOSTed()){
			//validatie moet nog even gemaakt worden. TODO dus nog.
			if(true){
				//opslaen
				if($this->groep->isAdmin()){
					$this->groep->setNaam($_POST['naam']);
					$this->groep->setSbeschrijving($_POST['sbeschrijving']);
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
				if(isset($_POST['naam'])){
					$this->groep->setNaam($_POST['naam']);
				}
				if(isset($_POST['sbeschrijving'])){
					$this->groep->setSbeschrijving($_POST['sbeschrijving']);
				}
				if(isset($_POST['beschrijving'])){
					$this->groep->setBeschrijving('beschrijving');
				}
			}
		}
	}
	
	/*
	 * Leden toevoegen aan een groep.
	 */
	public function action_addLid(){
		if(isset($_POST['naam'], $_POST['functie']) AND is_array($_POST['naam']) AND is_array($_POST['functie']) AND count($_POST['naam'])==count($_POST['functie'])){
			//nieuwe commissieleden erin stoppen.
			for($iTeller=0; $iTeller<count($_POST['naam']); $iTeller++){
				$success=true;
				if(preg_match('/^\w{4}$/', $_POST['naam'][$iTeller])){
					if(!$this->groep->addLid($_POST['naam'][$iTeller], $_POST['functie'][$iTeller])){
						//er gaat iets mis, zet $success op false;	
						$success=false;
					}
				}
			}
			if($success){
				$melding='Leden met succes toegevoegd.';
			}else{
				$melding='Niet alle leden met succes toegevoegd. Wellicht waren sommigen al lid van deze groep? (Groepcontroller::action_addLid())';
			}
		}else{
			$melding='Geen uid opgegeven';
		}
		$this->content->invokeRefresh($melding, $this->getUrl('default'));
	}
	public function action_delLid(){
		if(isset($queryparts[2])){
			
		}	
	}
	
}
?>
