<?php

namespace CsrDelft\view\maalcie\corvee\functies;

use CsrDelft\view\SmartyTemplateView;

/**
 * BeheerFunctiesView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van alle functies om te beheren.
 *
 */
class BeheerFunctiesView extends SmartyTemplateView {

	public function __construct(array $functies) {
		parent::__construct($functies, 'Beheer corveefuncties en kwalificaties');
	}

	public function view() {
		$this->smarty->assign('functies', $this->model);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/functie/beheer_functies.tpl');
	}

}
