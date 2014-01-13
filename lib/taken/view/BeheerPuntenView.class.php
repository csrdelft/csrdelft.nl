<?php

/**
 * BeheerPuntenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle corveepunten van alle leden.
 * 
 */
class BeheerPuntenView extends TemplateView {

	private $_leden_punten;
	private $_functies;

	public function __construct($leden_punten, $functies = null) {
		parent::__construct();
		$this->_leden_punten = $leden_punten;
		$this->_functies = $functies;
	}

	public function getTitel() {
		return 'Beheer corveepunten';
	}

	public function view() {
		if ($this->_functies === null) { // voor een lid
			$this->assign('puntenlijst', $this->_leden_punten);
			$this->display('taken/corveepunt/beheer_punten_lijst.tpl');
		} else { // matrix of repetities and voorkeuren
			$this->assign('melding', $this->getMelding());
			$this->assign('kop', $this->getTitel());
			$this->display('taken/menu_pagina.tpl');

			$this->assign('matrix', $this->_leden_punten);
			$this->assign('functies', $this->_functies);
			$this->display('taken/corveepunt/beheer_punten.tpl');
		}
	}

}

?>