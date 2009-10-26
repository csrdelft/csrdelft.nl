<?php
/*
 * class.documentcontroller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */

require_once 'class.controller.php';
require_once 'documenten/class.document.php';
require_once 'documenten/class.categorie.php';

require_once 'documenten/class.documentuploader.php';
require_once 'documenten/class.documentcontent.php';

class DocumentController extends Controller{

	public $document;

	public $baseurl='/communicatie/documenten_new/';

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
		$this->content->view();
		exit;
	}
	protected function action_categorie(){
		if($this->hasParam(1)){
			try{
				$categorie=new DocumentenCategorie($this->getParam(1));
			}catch(Exception $e){
				DocumentenCategorie::invokeRefresh('categorie bestaat niet');
			}
		}else{
			DocumentenCategorie::invokeRefresh('categorie bestaat niet');
		}

		$this->content=new DocumentCategorieContent($categorie);
	}
	
	protected function action_bewerken(){
		$this->loadDocument();
	}
	
	private $uploaders;	//array met uploaders.
	protected function action_toevoegen(){
		//maak een nieuw, leeg document aan.
		$this->document=new Document(0);

		if(isset($_POST['methode'])){
			$methode=$_POST['methode'];
		}else{
			if($this->document->hasFile()){
				$methode='DUKeepfile';
			}else{
				$methode='DUFileupload';
			}
		}
		$this->uploaders=DocumentUploader::getAll($methode, $this->document->hasFile());

		if($this->isPosted()){
			$this->document->setNaam($_POST['naam']);
			$this->document->setCatID($_POST['categorie']);

			//als we al een bestand hebben voor dit document, moet die natuurlijk eerst hdb.
			if($this->document->hasFile() AND !$this->uploaders['DUKeepfile']->isActive()){
				$this->document->deleteFile();
			}
			
			if($this->validate_document()){
				//Actieve methode selecteren.
				$uploader=$this->uploaders[$_POST['methode']];

				$this->document->setBestandsnaam($uploader->getFilename());
				$this->document->setSize($uploader->getSize());
				$this->document->setMimetype($uploader->getMimetype());
				
				if($this->document->save()){
					if($uploader->moveFile($this->document)){
						$melding='Document met succes toegevoegd';
					}else{
						$melding='Fout bij het opslaan van het bestand in het bestandsysteem';
					}
				}else{
					$melding='Fout bij toevoegen van document Document::save()';
				}
				DocumentContent::invokeRefresh($melding, $this->baseurl);
			}
		}else{
			if(isset($_GET['catID']) AND DocumentenCategorie::exists($_GET['catID'])){
				$this->document->setCatID($_GET['catID']);
			}
		}
		$this->content=new DocumentContent($this->document, $this->uploaders);
		$this->content->setMelding($this->errors);
		
		
	}
	
	private function validate_document(){
		if(isset($_POST['naam'], $_POST['categorie'])){
			if(strlen(trim($_POST['naam']))<3){
				$this->addError('Naam moet tenminste 3 tekens bevatten');
			}
			if(!(isset($_POST['methode']) AND array_key_exists($_POST['methode'], $this->uploaders))){
				$this->addError('Niet ondersteunde uploadmethode. Heeft u er wel een gekozen?');
			}else{
				if($_POST['methode']=='DUKeepfile' AND !$this->document->hasFile()){
					$this->addError('Dit document heeft nog geen bestand, dus dat kan ook niet behouden worden.');
				}
		
				//kijken of we errors hebben in de huidige methode.
				if(!$this->uploaders[$_POST['methode']]->valid()){
					$this->addError($this->uploaders[$_POST['methode']]->getErrors());
				}
				

					
			}
		}else{
			$this->addError('Formulier niet compleet');
		}
		return $this->valid;
	}
}

?>
