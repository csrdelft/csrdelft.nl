<?php

/**
 * BeheerFunctiesView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle functies om te beheren.
 * 
 */
class BeheerFunctiesView extends TemplateView {

	private $_functies;
	private $_popup;

	public function __construct($functies, $popup = null) {
		parent::__construct();
		$this->_functies = $functies;
		$this->_popup = $popup;
	}

	public function getTitel() {
		return 'Beheer corveefuncties en kwalificaties';
	}

	public function view() {
		if (is_array($this->_functies)) {
			$this->assign('popup', $this->_popup);
			$this->assign('melding', $this->getMelding());
			$this->assign('kop', $this->getTitel());
			$this->display('taken/menu_pagina.tpl');

			$this->assign('functies', $this->_functies);
			$this->display('taken/functie/beheer_functies.tpl');
		} elseif (is_int($this->_functies)) { // id of deleted corveefunctie
			echo '<tr id="corveefunctie-row-' . $this->_functies . '" class="remove"></tr>';
		} else {
			$this->assign('functie', $this->_functies);
			$this->display('taken/functie/beheer_functie_lijst.tpl');
		}
	}

}

?>