<?php

require_once 'MVC/controller/Controller.class.php';
require_once 'documenten/document.class.php';
require_once 'documenten/categorie.class.php';
require_once 'documenten/documentuploader.class.php';
require_once 'documenten/documentcontent.class.php';

/**
 * documentcontroller.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 */
class DocumentController extends Controller {

	public $document;
	public $baseurl = '/communicatie/documenten/';

	/**
	 * querystring:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($querystring) {
		parent::__construct($querystring);

		//wat zullen we eens gaan doen? Hier bepalen we welke actie we gaan uitvoeren
		//en of de ingelogde persoon dat mag.
		if (Loginlid::instance()->hasPermission('P_DOCS_READ')) {
			if ($this->hasParam(0) AND $this->getParam(0) != '') {
				$this->action = $this->getParam(0);
			} else {
				$this->action = 'recenttonen';
			}
			//niet alle acties mag iedereen doen, hier whitelisten voor de gebruikers
			//zonder P_DOCS_MOD, en gebruikers met, zodat bij niet bestaande acties
			//netjes gewoon het documentoverzicht getoond wordt.
			$allow = array('default', 'download', 'categorie');
			if (LoginLid::instance()->hasPermission('P_DOCS_MOD')) {
				$allow = array_merge($allow, array('bewerken', 'toevoegen', 'verwijderen'));
			}
			if (!in_array($this->action, $allow)) {
				$this->action = 'recenttonen';
			}
		} else {
			$this->action = 'geentoegang';
		}

		$this->performAction();
	}

	/**
	 * Wordt op diverse plekken geregeld.
	 */
	protected function hasPermission() {
		return true;
	}

	/**
	 * Ga er van uit dat in getParam(1) een documentid staat en laad dat in.
	 */
	private function loadDocument() {
		if ($this->hasParam(1)) {
			try {
				$this->document = new Document($this->getParam(1));
			} catch (Exception $e) {
				DocumentContent::invokeRefresh($this->baseurl, 'Geen geldig id opgegeven of een niet-bestaand document opgevraagd');
			}
		}
	}

	/**
	 * Recente documenten uit alle categorieën tonen
	 */
	protected function recenttonen() {
		$this->content = new DocumentenContent();
	}

	protected function verwijderen() {
		$this->loadDocument();
		try {
			if ($this->document->delete()) {
				DocumentContent::invokeRefresh($this->baseurl, 'Document is met succes verwijderd.', 1);
			} else {
				DocumentContent::invokeRefresh($this->baseurl, 'Document is niet verwijderd. Gaat mis in (Document::delete())');
			}
		} catch (Exception $e) {
			DocumentContent::invokeRefresh($this->baseurl, 'Document is niet verwijderd: ' . $e->getMessage());
		}
	}

	public function download() {
		$this->loadDocument();

		if ($this->document->hasFile()) {
			$this->content = new DocumentDownloadContent($this->document);
			$this->content->view();
		} else {
			DocumentContent::invokeRefresh($this->baseurl, 'Document heeft geen bestand, sorry voor het ongemak.');
		}
		exit;
	}

	protected function categorie() {
		if ($this->hasParam(1)) {
			try {
				$categorie = new DocumentenCategorie($this->getParam(1));
			} catch (Exception $e) {
				DocumentenCategorie::invokeRefresh(null, 'categorie bestaat niet');
			}
		} else {
			DocumentenCategorie::invokeRefresh(null, 'categorie bestaat niet');
		}

		$this->content = new DocumentCategorieContent($categorie);
	}

	protected function bewerken() {
		$this->loadDocument();
		$this->toevoegen(true);
	}

	private $uploaders; //array met uploaders.

	protected function toevoegen($edit = false) {
		if (!$edit) {
			//maak een nieuw, leeg document aan.
			$this->document = new Document(0);
		}

		if (isset($_POST['methode'])) {
			$methode = $_POST['methode'];
		} else {
			//pr($this->document); echo $this->document->hasFile() ? 'ja' : 'nee'; exit;
			if ($this->document->hasFile()) {
				$methode = 'DUKeepfile';
			} else {
				$methode = 'DUFileupload';
			}
		}
		$this->uploaders = DocumentUploader::getAll($this->document, $methode, $this->document->hasFile());

		if ($this->isPosted()) {
			$this->document->setNaam($_POST['naam']);
			$this->document->setCatID($_POST['categorie']);

			if ($this->validate_document()) {
				//als we al een bestand hebben voor dit document, moet die natuurlijk eerst hdb.
				if ($this->document->hasFile() AND $methode != 'DUKeepfile') {
					try {
						$this->document->deleteFile();
					} catch (Exception $e) {
						DocumentContent::invokeRefresh($this->baseurl, $e->getMessage());
					}
				}
				//Actieve methode selecteren.
				$uploader = $this->uploaders[$_POST['methode']];

				if ($methode !== 'DUKeepfile') {
					$this->document->setBestandsnaam($uploader->getFilename());
					$this->document->setSize($uploader->getSize());
					$this->document->setMimetype($uploader->getMimetype());
				}

				if ($this->document->save()) {
					try {
						if ($uploader->moveFile($this->document)) {
							$melding = array('Document met succes opgeslagen.', 1);
						} else {
							$melding = 'Fout bij het opslaan van het bestand in het bestandsysteem. Bewerk het document om het bestand alsnog toe te voegen.';
						}
					} catch (Exception $e) {
						$melding = 'Bestand aan document toevoegen mislukt: ' . $e->getMessage();
					}
				} else {
					$melding = 'Fout bij toevoegen van document Document::save()';
				}
				DocumentContent::invokeRefresh($this->baseurl, $melding);
			}
		} else {
			if (isset($_GET['catID']) AND DocumentenCategorie::exists($_GET['catID'])) {
				$this->document->setCatID($_GET['catID']);
			}
		}
		$this->content = new DocumentContent($this->document, $this->uploaders);
		$this->content->setMelding($this->errors);
	}

	private function validate_document() {
		if (isset($_POST['naam'], $_POST['categorie'])) {
			if (strlen(trim($_POST['naam'])) < 3) {
				$this->addError('Naam moet tenminste 3 tekens bevatten');
			}
			if (!(isset($_POST['methode']) AND array_key_exists($_POST['methode'], $this->uploaders))) {
				$this->addError('Niet ondersteunde uploadmethode. Heeft u er wel een gekozen?');
			} else {
				if ($_POST['methode'] == 'DUKeepfile' AND !$this->document->hasFile()) {
					$this->addError('Dit document heeft nog geen bestand, dus dat kan ook niet behouden worden.');
				}
				//kijken of we errors hebben in de huidige methode.
				if (!$this->uploaders[$_POST['methode']]->valid()) {
					$this->addError($this->uploaders[$_POST['methode']]->getErrors());
				}
			}
		} else {
			$this->addError('Formulier niet compleet');
		}
		return $this->valid;
	}

}

?>