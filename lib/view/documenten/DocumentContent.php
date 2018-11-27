<?php

namespace CsrDelft\view\documenten;

use CsrDelft\model\entity\documenten\Document;
use CsrDelft\view\View;

/**
 * Document bekijken.
 * Ongeldig aangevraagde documenten worden in de controller afgehandeld.
 */
class DocumentContent implements View {

	private $model;

	public function __construct(Document $document) {
		$this->model = $document;
	}

	public function view() {
		$mime = $this->model->mimetype;
		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		header('Content-Type: ' . $mime);
		if (!strstr($mime, 'image') AND !strstr($mime, 'text')) {
			header('Content-Disposition: inline; filename="' . $this->model->filename . '";');
			header('Content-Lenght: ' . $this->model->filesize . ';');
		}
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
