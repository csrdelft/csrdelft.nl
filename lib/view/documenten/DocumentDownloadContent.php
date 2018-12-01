<?php

namespace CsrDelft\view\documenten;

use CsrDelft\model\entity\documenten\Document;
use CsrDelft\view\View;

/**
 * Document downloaden, allemaal headers goedzetten.
 * Ongeldig aangevraagde documenten worden in de controller afgehandeld.
 */
class DocumentDownloadContent implements View {

	private $model;

	public function __construct(Document $document) {
		$this->model = $document;
	}

	public function view() {
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $this->model->mimetype);
		header('Content-Disposition: attachment; filename="' . $this->model->filename . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $this->model->filesize);
		readfile($this->model->getFullPath());
	}

	public function getTitel() {
		return $this->model->filename;
	}

	public function getBreadcrumbs() {
		return '';
	}

	public function getModel() {
		return $this->model;
	}
}
