<?php

/*
 * class.documentcontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once 'document.class.php';

/*
 * Weergeven van één document, bijvoorbeeld toevoegen/bewerken.
 */

class DocumentContent extends TemplateView {

	private $document;
	private $uploaders;

	public function __construct(Document $document, $uploaders) {
		parent::__construct();
		$this->document = $document;
		$this->uploaders = $uploaders;
	}

	public function getTitel() {
		if ($this->document->getID() == 0) {
			return 'Document toevoegen';
		} else {
			return 'Document bewerken';
		}
	}

	public function view() {
		$this->smarty->assign('categorieen', DocumentenCategorie::getAll());
		$this->smarty->assign('document', $this->document);
		$this->smarty->assign('uploaders', $this->uploaders);
		$this->smarty->display('documenten/document.tpl');
	}

}

/*
 * Overzicht van alle categorieën met een bepaald aantal documenten per
 * categorie, zeg maar de standaarpagina voor de documentenketzer.
 */

class DocumentenContent extends TemplateView {

	public function getTitel() {
		return 'Documentenketzer';
	}

	public function view() {
		$this->smarty->assign('categorieen', DocumentenCategorie::getAll());
		$this->smarty->display('documenten/documenten.tpl');
	}

}

/*
 * Documenten voor een bepaalde categorie tonen.
 */

class DocumentCategorieContent extends TemplateView {

	private $categorie;

	public function __construct(DocumentenCategorie $categorie) {
		parent::__construct();
		$this->categorie = $categorie;
	}

	public function getTitel() {
		return 'Documenten in categorie: ' . $this->categorie->getNaam();
	}

	public function view() {
		$this->smarty->assign('categorie', $this->categorie);
		$this->smarty->display('documenten/documentencategorie.tpl');
	}

}

/*
 * Document downloaden, allemaal headers goedzetten.
 * Ongeldig aangevraagde documenten worden in de controller afgehandeld.
 */

class DocumentDownloadContent extends TemplateView {

	private $document;

	public function __construct(Document $document) {
		parent::__construct();
		$this->document = $document;
	}

	public function view() {
		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('content-type: ' . $this->document->getMimeType());

		$mime = $this->document->getMimetype();
		if (!strstr($mime, 'image') AND !strstr($mime, 'text')) {
			header('Content-Disposition: attachment; filename="' . $this->document->getBestandsnaam() . '";');
			header('Content-Lenght: ' . $this->document->getSize() . ';');
		}
		readfile($this->document->getFullPath());
	}

}

class DocumentUbbContent extends TemplateView {

	private $document;

	public function __construct(Document $document) {
		parent::__construct();
		$this->document = $document;
	}

	public function getHTML() {
		$this->smarty->assign('document', $this->document);
		return $this->smarty->fetch('documenten/document.ubb.tpl');
	}

	public function view() {
		echo $this->getHTML();
	}

}
