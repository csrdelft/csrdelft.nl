<?php
/*
 * class.documentcontroller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */

require_once 'class.controller.php';
require_once 'documenten/class.document.php';
require_once 'documenten/class.categorie.php';

require_once 'documenten/class.documentcontent.php';

class DocumentController extends Controller{

	public $document;

	public static $baseurl='/communicatie/documenten_new/';

	/*
	 * querystring:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($querystring){
		parent::__construct($querystring);
		
		//wat zullen we eens gaan doen? Hier bepalen we welke actie we gaan uitvoeren
		//en of de ingelogde persoon dat mag.
		if(Loginlid::instance()->hasPermission('P_DOCS_READ')){
			if($this->hasParam(0) AND $this->getParam(0)!=''){
				$this->action=$this->getParam(0);
			}else{
				$this->action='default';
			}
			//niet alle acties mag iedereen doen, hier whitelisten voor de gebruikers
			//zonder P_DOCS_MOD, en gebruikers met, zodat bij niet bestaande acties
			//netjes gewoon het documentoverzicht getoond wordt.
			$allow=array('default', 'download', 'categorie');
			if(LoginLid::instance()->hasPermission('P_DOCS_MOD')){
				$allow=array_merge($allow, array('bewerken', 'toevoegen', 'verwijderen'));
			}
			if(!in_array($this->action, $allow)){
				$this->action='default';
			}
		}else{
			$this->action='geentoegang';
		}

		$this->performAction();
	}
	
	//ga er van uit dat in getParam(1) een documentid staat en laad dat in.
	private function loadDocument(){
		if($this->hasParam(1)){
			$this->document=new Document($this->getParam(1));
		}
		if(!$this->document instanceof Document){
			DocumentContent::invokeRefresh('Geen geldig id opgegeven of een niet-bestaand document opgevraagd', $this->baseurl);
		}
	}
	/*
	 * Recente documenten uit alle categorieën tonen
	 */
	protected function action_default(){
		$this->content=new DocumentenContent();
	}
	
	protected function action_verwijderen(){
		$this->loadDocument();
	}
	public function action_download(){
		$this->loadDocument();
		$this->content=new DocumentDownloadContent($this->document);
	}
	protected function action_categorie(){

	}
	protected function action_bewerken(){
		$this->loadDocument();
	}
	protected function action_toevoegen(){
		//maak een nieuw, leeg document aan.
		$this->document=new Document(0);
		
		if($this->isPosted()){
			$this->document->setNaam($_POST['naam']);
			$this->document->setCatID($_POST['categorie']);
			if($this->document_validator()){
				if($this->document->save()){
					$melding='Document met succes toegevoegd';
				}else{
					$melding='Fout bij toevoegen van document Document::save()';
				}
				DocumentContent::invokeRefresh($melding, $this->baseurl);
			}
		}
		$this->content=new DocumentContent($this->document);
		
	}
	private function document_validator(){
		if(isset($_POST['naam'], $_POST['categorie'])){
			return false;
		}
		return false;
	}
}

?>
