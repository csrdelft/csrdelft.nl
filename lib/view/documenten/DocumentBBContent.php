<?php

namespace CsrDelft\view\documenten;

use CsrDelft\model\entity\documenten\Document;

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
