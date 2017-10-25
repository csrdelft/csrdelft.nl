<?php

namespace CsrDelft\view\maalcie\corvee\punten;

use CsrDelft\view\SmartyTemplateView;

class BeheerPuntenLidView extends SmartyTemplateView {

	public function __construct(array $puntenlijst) {
		parent::__construct($puntenlijst);
	}

	public function view() {
		$this->smarty->assign('puntenlijst', $this->model);
		$this->smarty->display('maalcie/corveepunt/beheer_punten_lijst.tpl');
	}

}
