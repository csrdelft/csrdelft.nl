<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\view\SmartyTemplateView;
use CsrDelft\view\View;

class BeheerMaaltijdenBeoordelingenView extends SmartyTemplateView {
	public function __construct(View $model, $titel = false) {
		parent::__construct($model, $titel);
	}

	public function view() {
		$this->smarty->display('maalcie/maaltijd/maaltijd_beoordelingen.tpl');
		$this->model->view();
	}
}
