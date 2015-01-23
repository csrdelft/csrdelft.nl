<?php

require_once 'model/documenten/Document.class.php';

/**
 * DocumentenView.class.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Overzicht van alle categorieën met een bepaald aantal documenten per
 * categorie, zeg maar de standaarpagina voor de documentenketzer.
 */
abstract class DocumentenView extends SmartyTemplateView {

	public function getBreadcrumbs() {
		return '<a href="/documenten" title="Documenten"><span class="fa fa-file module-icon"></span></a>';
	}

}

class DocumentenContent extends DocumentenView {

	public function __construct() {
		$cats = array();
		foreach (DocCategorie::getAll() as $cat) {
			if ($cat->magBekijken()) {
				$cats[] = $cat;
			}
		}
		parent::__construct($cats, 'Documentenketzer');
	}

	public function view() {
		$this->smarty->assign('categorieen', $this->model);
		$this->smarty->display('documenten/documenten.tpl');
	}

}

/**
 * Documenten voor een bepaalde categorie tonen.
 */
class DocumentCategorieContent extends DocumentenView {

	public function __construct(DocCategorie $categorie) {
		parent::__construct($categorie, 'Documenten in categorie: ' . $categorie->getNaam());
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <span class="active">' . $this->model->getNaam() . '</span>';
	}

	public function view() {
		$this->smarty->assign('categorie', $this->model);
		$this->smarty->display('documenten/documentencategorie.tpl');
	}

}

/**
 * Document bekijken.
 * Ongeldig aangevraagde documenten worden in de controller afgehandeld.
 */
class DocumentContent extends DocumentenView {

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
			header('Content-Disposition: inline; filename="' . $this->model->getFileName() . '";');
			header('Content-Lenght: ' . $this->model->getFileSize() . ';');
		}
		readfile($this->model->getFullPath());
	}

}

/**
 * Document downloaden, allemaal headers goedzetten.
 * Ongeldig aangevraagde documenten worden in de controller afgehandeld.
 */
class DocumentDownloadContent extends DocumentenView {

	public function __construct(Document $document) {
		parent::__construct($document);
	}

	public function view() {
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $this->model->getMimetype());
		header('Content-Disposition: attachment; filename="' . $this->model->getFileName() . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $this->model->getFileSize());
		readfile($this->model->getFullPath());
	}

}

class DocumentBBContent extends DocumentenView {

	public function __construct(Document $document) {
		parent::__construct($document);
	}

	public function getHtml() {
		$this->smarty->assign('document', $this->model);
		return $this->smarty->fetch('documenten/document.bb.tpl');
	}

	public function view() {
		echo $this->getHtml();
	}

}
