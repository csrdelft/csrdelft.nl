<?php

require_once 'documenten/document.class.php';
require_once 'documenten/categorie.class.php';
require_once 'documenten/documentcontent.class.php';

/**
 * documentcontroller.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 */
class DocumentController extends Controller {

	public $document;
	public $baseurl;
	protected $valid = true;
	protected $errors = '';

	/**
	 * querystring:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($querystring) {
		parent::__construct($querystring, null);
		$this->baseurl = CSR_ROOT . '/communicatie/documenten/';
		//wat zullen we eens gaan doen? Hier bepalen we welke actie we gaan uitvoeren
		//en of de ingelogde persoon dat mag.
		if (LoginModel::mag('P_DOCS_READ')) {
			if ($this->hasParam(0)) {
				$this->action = $this->getParam(0);
			} else {
				$this->action = 'recenttonen';
			}
			//niet alle acties mag iedereen doen, hier whitelisten voor de gebruikers
			//zonder P_DOCS_MOD, en gebruikers met, zodat bij niet bestaande acties
			//netjes gewoon het documentoverzicht getoond wordt.
			$allow = array('default', 'download', 'categorie');
			if (LoginModel::mag('P_DOCS_MOD')) {
				$allow = array_merge($allow, array('bewerken', 'toevoegen', 'verwijderen'));
			}
			if (!in_array($this->action, $allow)) {
				$this->action = 'recenttonen';
			}
		} else {
			$this->action = 'geentoegang';
		}
	}

	/**
	 * Wordt op diverse plekken geregeld.
	 */
	protected function mag($action) {
		return true;
	}

	public function addError($error) {
		$this->valid = false;
		$this->errors .= $error . '<br />';
	}

	/**
	 * Ga er van uit dat in getParam(1) een documentid staat en laad dat in.
	 */
	private function loadDocument() {
		if ($this->hasParam(1)) {
			try {
				$this->document = new Document($this->getParam(1));
			} catch (Exception $e) {
				SimpleHTML::setMelding('Geen geldig id opgegeven of een niet-bestaand document opgevraagd', -1);
				redirect($this->baseurl);
			}
		}
	}

	/**
	 * Recente documenten uit alle categorieën tonen
	 */
	protected function recenttonen() {
		$this->view = new DocumentenContent();
	}

	protected function verwijderen() {
		$this->loadDocument();
		try {
			if ($this->document->delete()) {
				SimpleHTML::setMelding('Document is met succes verwijderd.', 1);
				redirect($this->baseurl);
			} else {
				SimpleHTML::setMelding('Document is niet verwijderd. Gaat mis in (Document::delete())', -1);
				redirect($this->baseurl);
			}
		} catch (Exception $e) {
			SimpleHTML::setMelding('Document is niet verwijderd: ' . $e->getMessage(), -1);
			redirect($this->baseurl);
		}
	}

	public function download() {
		$this->loadDocument();
		if ($this->document->hasFile()) {
			$this->view = new DocumentDownloadContent($this->document);
			$this->view->view();
		} else {
			SimpleHTML::setMelding('Document heeft geen bestand, sorry voor het ongemak.', -1);
			redirect($this->baseurl);
		}
		exit;
	}

	protected function categorie() {
		if ($this->hasParam(1)) {
			try {
				$categorie = new DocumentenCategorie($this->getParam(1));
			} catch (Exception $e) {
				SimpleHTML::setMelding('categorie bestaat niet', -1);
				redirect(null);
			}
		} else {
			SimpleHTML::setMelding('categorie bestaat niet', -1);
			redirect(null);
		}
		$this->view = new DocumentCategorieContent($categorie);
	}

	protected function bewerken() {
		$this->loadDocument();
		$this->toevoegen(true);
	}

	protected function toevoegen($edit = false) {
		if (!$edit) {
			//maak een nieuw, leeg document aan.
			$this->document = new Document(0);
		}
		if (isset($_GET['catID']) AND DocumentenCategorie::exists($_GET['catID'])) {
			$this->document->setCatID($_GET['catID']);
		}
		$namen = array();
		foreach (DocumentenCategorie::getAll() as $cat) {
			$namen[$cat->getID()] = $cat->getNaam();
		}
		$bestand = $this->document->getBestand();
		if (!file_exists($bestand->directory . $bestand->filename)) {
			$bestand = null;
		}
		$fields['catID'] = new SelectField('catID', $this->document->getCatID(), 'Categorie', $namen);
		$fields['naam'] = new RequiredTextField('naam', $this->document->getNaam(), 'Documentnaam');
		$fields['uploader'] = new RequiredFileField('document', $bestand, 'documenten/');
		$fields['btn'] = new FormButtons('/communicatie/documenten/');
		$formulier = new Formulier(null, 'documentForm', '/communicatie/documenten/bewerken/' . $this->document->getId());
		$formulier->addFields($fields);
		if ($this->document->getID() == 0) {
			$formulier->titel = 'Document toevoegen';
			$fields['btn']->resetIcon = null;
			$fields['btn']->resetText = null;
		} else {
			$formulier->titel = 'Document bewerken';
		}
		if ($this->isPosted() AND $formulier->validate()) {
			$this->document->setNaam($fields['naam']->getValue());
			$this->document->setCatID($fields['catID']->getValue());
			// Als we al een bestand hebben voor dit document, moet die natuurlijk eerst hdb.
			if ($fields['uploader']->getType() !== 'BestandBehouden') {
				if ($this->document->hasFile()) {
					try {
						$this->document->deleteFile();
					} catch (Exception $e) {
						SimpleHTML::setMelding($e->getMessage(), -1);
						redirect($this->baseurl);
					}
				}
				$bestand = $fields['uploader']->getModel();
				$this->document->setFileName($bestand->filename);
				$this->document->setFileSize($bestand->filesize);
				$this->document->setMimetype($bestand->mimetype);
			}
			if ($this->document->save()) {
				try {
					if ($fields['uploader']->opslaan($this->document->getPath(), $this->document->getFullFileName())) {
						SimpleHTML::setMelding('Document met succes opgeslagen.', 1);
					} else {
						SimpleHTML::setMelding('Fout bij het opslaan van het bestand in het bestandsysteem. Bewerk het document om het bestand alsnog toe te voegen.', -1);
					}
				} catch (Exception $e) {
					SimpleHTML::setMelding('Bestand van document opslaan mislukt: ' . $e->getMessage(), -1);
				}
			} else {
				SimpleHTML::setMelding('Fout bij toevoegen van document Document::save()', -1);
			}
			redirect($this->baseurl);
		}
		SimpleHTML::setMelding($this->errors, -1);
		$this->view = $formulier;
	}

}
