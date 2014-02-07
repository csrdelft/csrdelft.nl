<?php

/**
 * CorveeRoosterView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van het corveerooster.
 * 
 */
class CorveeRoosterView extends TemplateView {

	private $_rooster;
	private $_toonverleden;

	public function __construct($rooster, $toonverleden = false) {
		parent::__construct();
		$this->_rooster = $rooster;
		$this->_toonverleden = $toonverleden;
	}

	public function getTitel() {
		return 'Corveerooster';
	}

	public function view() {
		$this->smarty->display('taken/menu_pagina.tpl');

		$this->smarty->assign('rooster', $this->_rooster);
		$this->smarty->assign('toonverleden', $this->_toonverleden);
		$this->smarty->display('taken/corveetaak/corvee_rooster.tpl');
	}

}
