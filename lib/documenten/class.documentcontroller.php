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

	private $downloadfile;
	
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


			//als we al een bestand hebben voor dit document, moet die natuurlijk eerst hdb.
			if($this->document->hasFile() AND !$_POST['methode']!='keepfile'){
				$this->document->deleteFile();
			}
			
			if($this->validate_document()){
				switch($_POST['methode']){
					case 'uploaden':
						$this->document->setMimetype($this->downloadfile['type']);
						$this->document->setSize($this->downloadfile['size']);
						$this->document->setBestandsnaam($this->downloadfile['name']);
					break;
					case 'fromurl':
						$this->document->setSize(strlen($this->downloadfile));
						$naam=substr(trim($_POST['url']), strrpos($_POST['url'], '/')+1);
						if(strlen($naam)<3){
							$naam=$this->document->getNaam();
						}
						$naam=preg_replace("/[^a-zA-Z0-9\s\.\-\_]/", '', $naam);
						$this->document->setBestandsnaam($naam);
						
					break;
					case 'publicftp':

					break;
				}

				if($this->document->save()){
					if($this->move_document()){
						$melding='Document met succes toegevoegd';
					}else{
						$melding='Fout bij het opslaan van het bestand in het bestandsysteem';
					}
				}else{
					$melding='Fout bij toevoegen van document Document::save()';
				}
				DocumentContent::invokeRefresh($melding, $this->baseurl);
			}
		}
		$this->content=new DocumentContent($this->document);
		$this->content->setMelding($this->errors);
		
		
	}
	private function move_document(){
		switch($_POST['methode']){
			case 'fromurl':
				return $this->document->putFile($this->downloadfile);
			break;
			case 'uploaden':
				return $this->document->moveUploaded($this->downloadfile['tmp_name']);
			break;
			case 'publicftp':
			break;
		}
		return false;
	}
	
	private function validate_document(){
		if(isset($_POST['naam'], $_POST['categorie'])){
			if(strlen(trim($_POST['naam']))<3){
				$this->addError('Naam moet tenminste 3 tekens bevatten');
			}
			$allowed=array('fromurl', 'uploaden', 'publicftp');
			if(!(isset($_POST['methode']) AND in_array($_POST['methode'], $allowed))){
				$this->addError('Niet ondersteunde uploadmethode. Heeft u er wel een gekozen?');
			}else{
				switch($_POST['methode']){
					case 'fromurl':
						if(!isset($_POST['url'])){
							$this->addError('Formulier niet compleet');
						}
						$this->downloadfile=file_get_contents($_POST['url']);
					break;
					case 'uploaden':
						if(!isset($_FILES['file_upload'])){
							$this->addError('Formulier niet compleet');
						}
						$this->downloadfile=$_FILES['file_upload'];
						if($this->downloadfile['error']!=0){
							$this->addError('Upload-error: error-code: '.$this->downloadfile['error']);
						}	
					break;
					case 'publicftp':
						
					break;
					default:
						$this->addError('Niet ondersteunde methode.');
				}
			}
		}else{
			$this->addError('Formulier niet compleet');
		}
		return $this->valid;
	}
}

?>
