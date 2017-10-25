<?php

namespace CsrDelft\view\documenten;

use CsrDelft\model\documenten\DocumentCategorieModel;

class DocumentenContent extends DocumentenView {

	public function __construct() {
		parent::__construct(DocumentCategorieModel::instance(), 'Documentenketzer');
	}

	public function view() {
		$this->smarty->assign('categorieen', $this->model->find());
		$this->smarty->assign('model', $this->model);
		$this->smarty->display('documenten/documenten.tpl');
	}

}
