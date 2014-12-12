<?php

require_once 'model/documenten/Document.class.php';
require_once 'model/documenten/DocCategorie.class.php';
require_once 'view/DocumentenView.class.php';

/**
 * documentcontroller.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 */
class DocumentenController extends Controller {

	public $document;
	public $baseurl;
	protected $valid = true;
	protected $errors = '';

	/**
	 * querystring:
	 *
	 * actie[/id[/opties]]
	 */
	public function __construct($query) {
		parent::__construct($query, null);
		$this->baseurl = CSR_ROOT . '/documenten/';
		//wat zullen we eens gaan doen? Hier bepalen we welke actie we gaan uitvoeren
		//en of de ingelogde persoon dat mag.
		if (LoginModel::mag('P_DOCS_READ')) {
			if ($this->hasParam(2)) {
				$this->action = $this->getParam(2);
			} else {
				$this->action = 'recenttonen';
			}
			//niet alle acties mag iedereen doen, hier whitelisten voor de gebruikers
			//zonder P_DOCS_MOD, en gebruikers met, zodat bij niet bestaande acties
			//netjes gewoon het documentoverzicht getoond wordt.
			$allow = array('recenttonen', 'bekijken', 'download', 'categorie');
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

	public function performAction(array $args = array()) {
		parent::performAction($args);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('datatable');
		$this->view->addCompressedResources('documenten');
	}

	/**
	 * Wordt op diverse plekken geregeld.
	 */
	protected function mag($action, $resource) {
		return true;
	}

	public function addError($error) {
		$this->valid = false;
		$this->errors .= $error . '<br />';
	}

	/**
	 * Ga er van uit dat in getParam(3) een documentid staat en laad dat in.
	 */
	private function loadDocument() {
		if ($this->hasParam(3)) {
			try {
				$this->document = new Document($this->getParam(3));
			} catch (Exception $e) {
				setMelding('Geen geldig id opgegeven of een niet-bestaand document opgevraagd', -1);
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
				setMelding('Document is met succes verwijderd', 1);
			} else {
				setMelding('Document is niet verwijderd. Gaat mis in (Document::delete())', -1);
			}
		} catch (Exception $e) {
			setMelding('Document is niet verwijderd: ' . $e->getMessage(), -1);
		}
		redirect('/documenten/' . $this->document->getCatID());
	}

	public function bekijken() {
		$this->loadDocument();
		if (!$this->document->magBekijken()) {
			$this->geentoegang();
		}
		if ($this->document->hasFile()) {
			$this->view = new DocumentContent($this->document);
			$this->view->view();
		} else {
			setMelding('Document heeft geen bestand.', -1);
			redirect($this->baseurl);
		}
		exit;
	}

	public function download() {
		$this->loadDocument();
		if (!$this->document->magBekijken()) {
			$this->geentoegang();
		}
		if ($this->document->hasFile()) {
			$this->view = new DocumentDownloadContent($this->document);
			$this->view->view();
		} else {
			setMelding('Document heeft geen bestand.', -1);
			redirect($this->baseurl);
		}
		exit;
	}

	protected function categorie() {
		if ($this->hasParam(3)) {
			try {
				$categorie = new DocCategorie($this->getParam(3));
			} catch (Exception $e) {
				setMelding('Categorie bestaat niet', -1);
				redirect(null);
			}
		} else {
			setMelding('Categorie bestaat niet', -1);
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
		$formulier = new Formulier(null, 'documentForm', '/documenten/bewerken/' . $this->document->getId());
		$this->view = $formulier;
		if (isset($_GET['catID']) AND DocCategorie::exists($_GET['catID'])) {
			$this->document->setCatID($_GET['catID']);
		}
		$namen = array();
		foreach (DocCategorie::getAll() as $cat) {
			$namen[$cat->getID()] = $cat->getNaam();
		}
		$bestand = $this->document->getBestand();
		if (!file_exists($bestand->directory . $bestand->filename)) {
			$bestand = null;
		}
		$map = new Map();
		$map->path = PUBLIC_FTP . 'documenten/';
		$map->dirname = basename($map->path);
		$fields['catID'] = new SelectField('catID', $this->document->getCatID(), 'Categorie', $namen);
		$fields['naam'] = new RequiredTextField('naam', $this->document->getNaam(), 'Documentnaam');
		$fields['uploader'] = new RequiredFileField('document', 'Document', $bestand, $map);
		$fields['rechten'] = new RechtenField('leesrechten', $this->document->getLeesrechten(), 'Leesrechten');
		$fields['rechten']->readonly = true;
		$fields['btn'] = new FormDefaultKnoppen('/documenten/');
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
			if (get_class($fields['uploader']->getUploader()) !== 'BestandBehouden') {
				if ($this->document->hasFile()) {
					try {
						$this->document->deleteFile();
					} catch (Exception $e) {
						setMelding('Bestaand document verwijderen mislukt: ' . $e->getMessage(), -1);
						return;
					}
				}
				$bestand = $fields['uploader']->getModel();
				$this->document->setFileName($bestand->filename);
				$this->document->setFileSize($bestand->filesize);
				$this->document->setMimetype($bestand->mimetype);
				$this->document->setLeesrechten($fields['rechten']->getValue());
			}
			if ($this->document->save()) {
				try {
					$fields['uploader']->opslaan($this->document->getPath(), $this->document->getFullFileName());
					setMelding('Document met succes opgeslagen.', 1);
					redirect('/documenten/categorie/' . $this->document->getCatID());
				} catch (Exception $e) {
					setMelding('Bestand van document opslaan mislukt: ' . $e->getMessage(), -1);
				}
			} else {
				setMelding('Fout bij toevoegen van document Document::save()', -1);
			}
		} else {
			//setMelding(print_r($formulier->getError(), true), -1);
		}
		setMelding($this->errors, -1);
	}

}
