<?php

namespace CsrDelft\view\maalcie\corvee\vrijstellingen;

use CsrDelft\view\SmartyTemplateView;

/**
 * BeheerVrijstellingenView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van alle vrijstellingen om te beheren.
 *
 */
class BeheerVrijstellingenView extends SmartyTemplateView {

	public function __construct($vrijstellingen) {
		parent::__construct($vrijstellingen, 'Beheer vrijstellingen');
	}

	public function view() {
		$this->smarty->assign('vrijstellingen', $this->model);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/vrijstelling/beheer_vrijstellingen.tpl');
	}

}
