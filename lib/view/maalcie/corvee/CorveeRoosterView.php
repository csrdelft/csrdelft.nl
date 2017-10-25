<?php

namespace CsrDelft\view\maalcie\corvee;

use CsrDelft\view\SmartyTemplateView;

/**
 * CorveeRoosterView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van het corveerooster.
 *
 */
class CorveeRoosterView extends SmartyTemplateView {

	private $toonverleden;

	public function __construct($rooster, $toonverleden = false) {
		parent::__construct($rooster, 'Corveerooster');
		$this->toonverleden = $toonverleden;
	}

	public function view() {
		$this->smarty->assign('rooster', $this->model);
		$this->smarty->assign('toonverleden', $this->toonverleden);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/corveetaak/corvee_rooster.tpl');
	}

}
