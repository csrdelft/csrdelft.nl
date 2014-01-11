<?php

/**
 * MaaltijdRepetitiesView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle maaltijd-repetities om te beheren.
 * 
 */
class MaaltijdRepetitiesView extends TemplateView {

	private $_repetities;
	private $_popup;

	public function __construct($repetities, $popup = null) {
		parent::__construct();
		$this->_repetities = $repetities;
		$this->_popup = $popup;
	}

	public function getRepetitie() {
		return $this->_repetities;
	}

	public function getTitel() {
		return 'Beheer maaltijdrepetities';
	}

	public function view() {
		if (is_array($this->_repetities)) { // list of repetities
			$this->assign('popup', $this->_popup);
			$this->assign('melding', $this->getMelding());
			$this->assign('kop', $this->getTitel());
			$this->display('taken/menu_pagina.tpl');

			$this->assign('repetities', $this->_repetities);
			$this->display('taken/maaltijd-repetitie/beheer_maaltijd_repetities.tpl');
		} elseif (is_int($this->_repetities)) { // id of deleted repetitie
			echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
			echo '<tr id="repetitie-row-' . $this->_repetities . '" class="remove"></tr>';
		} else { // single repetitie
			echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
			$this->assign('repetitie', $this->_repetities);
			$this->display('taken/maaltijd-repetitie/beheer_maaltijd_repetitie_lijst.tpl');
		}
	}

}

?>