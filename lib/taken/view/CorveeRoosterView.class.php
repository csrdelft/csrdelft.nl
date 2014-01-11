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
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->display('taken/menu_pagina.tpl');

		$this->assign('rooster', $this->_rooster);
		$this->assign('toonverleden', $this->_toonverleden);
		$this->display('taken/corveetaak/corvee_rooster.tpl');
	}

}

?>