<?php

namespace CsrDelft\view\maalcie\corvee\voorkeuren;

use CsrDelft\view\SmartyTemplateView;

/**
 * BeheerVoorkeurenView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van alle voorkeuren van alle leden.
 *
 */
class BeheerVoorkeurenView extends SmartyTemplateView {

	private $repetities;

	public function __construct(array $matrix, $repetities) {
		parent::__construct($matrix, 'Beheer voorkeuren');
		$this->repetities = $repetities;
	}

	public function view() {
		$this->smarty->assign('matrix', $this->model);
		$this->smarty->assign('repetities', $this->repetities);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/voorkeur/beheer_voorkeuren.tpl');
	}

}
