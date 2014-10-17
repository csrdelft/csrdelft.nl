<?php

require_once 'document.class.php';

/**
 * documentcontent.class.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Overzicht van alle categorieën met een bepaald aantal documenten per
 * categorie, zeg maar de standaarpagina voor de documentenketzer.
 */
class DocumentenContent extends SmartyTemplateView {

	public function __construct() {
		parent::__construct(DocumentenCategorie::getAll(), 'Documentenketzer');
	}

	public function view() {
		$this->smarty->assign('categorieen', $this->model);
		$this->smarty->display('documenten/documenten.tpl');
	}

}

/**
 * Documenten voor een bepaalde categorie tonen.
 */
class DocumentCategorieContent extends SmartyTemplateView {

	public function __construct(DocumentenCategorie $categorie) {
		parent::__construct($categorie, 'Documenten in categorie: ' . $categorie->getNaam());
	}

	public function view() {
		$this->smarty->assign('categorie', $this->model);
		$this->smarty->display('documenten/documentencategorie.tpl');
	}

}

/**
 * Document downloaden, allemaal headers goedzetten.
 * Ongeldig aangevraagde documenten worden in de controller afgehandeld.
 */
class DocumentDownloadContent extends SmartyTemplateView {

	public function __construct(Document $document) {
		parent::__construct($document);
	}

	public function view() {
		$mime = $this->model->getMimetype();
		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('Content-Type: ' . $mime);
		if (!strstr($mime, 'image') AND ! strstr($mime, 'text')) {
			header('Content-Disposition: attachment; filename="' . $this->model->getFileName() . '";');
			header('Content-Lenght: ' . $this->model->getFileSize() . ';');
		}
		readfile($this->model->getFullPath());
	}

}

class DocumentBBContent extends SmartyTemplateView {

	public function __construct(Document $document) {
		parent::__construct($document);
	}

	public function getHTML() {
		$this->smarty->assign('document', $this->model);
		return $this->smarty->fetch('documenten/document.ubb.tpl');
	}

	public function view() {
		echo $this->getHTML();
	}

}
