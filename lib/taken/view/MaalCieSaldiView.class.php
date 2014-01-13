<?php

/**
 * MaalCieSaldiView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van een upload tool voor het bijwerken de MaalCie saldi op de stek.
 * 
 */
class MaalCieSaldiView extends TemplateView {

	private $_melding;

	public function __construct($melding = false) {
		parent::__construct();
		$this->_melding = $melding;
	}

	public function getTitel() {
		return 'MaalCie-saldi uploaden met een CSV-bestand';
	}

	public function view() {
		if ($this->_melding) {
			echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
		} else {
			$this->assign('melding', $this->getMelding());
			$this->assign('kop', $this->getTitel());
			$this->display('taken/menu_pagina.tpl');
			$this->display('taken/maalcie_saldi.tpl');
		}
	}

}

?>