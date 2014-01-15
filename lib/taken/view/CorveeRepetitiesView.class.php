<?php

/**
 * CorveeRepetitiesView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle corvee-repetities om te beheren.
 * 
 */
class CorveeRepetitiesView extends TemplateView {

	private $_repetities;
	private $_maaltijdrepetitie;
	private $_popup;

	public function __construct($repetities, $maaltijdrepetitie = null, $popup = null) {
		parent::__construct();
		$this->_repetities = $repetities;
		$this->_maaltijdrepetitie = $maaltijdrepetitie;
		$this->_popup = $popup;
	}

	public function getRepetitie() {
		return $this->_repetities;
	}

	public function getTitel() {
		if ($this->_maaltijdrepetitie !== null) {
			return 'Corveebeheer maaltijdrepetitie: ' . $this->_maaltijdrepetitie->getStandaardTitel();
		}
		return 'Beheer corveerepetities';
	}

	public function view() {
		if ($this->_maaltijdrepetitie !== null) {
			$this->smarty->assign('maaltijdrepetitie', $this->_maaltijdrepetitie);
		}
		if (is_array($this->_repetities)) { // list of repetities
			$this->smarty->assign('popup', $this->_popup);
			$this->smarty->assign('melding', $this->getMelding());
			$this->smarty->assign('kop', $this->getTitel());
			$this->smarty->display('taken/menu_pagina.tpl');

			$this->smarty->assign('repetities', $this->_repetities);
			$this->smarty->display('taken/corvee-repetitie/beheer_corvee_repetities.tpl');
		} elseif (is_int($this->_repetities)) { // id of deleted repetitie
			echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
			echo '<tr id="repetitie-row-' . $this->_repetities . '" class="remove"></tr>';
		} else { // single repetitie
			echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
			$this->smarty->assign('repetitie', $this->_repetities);
			$this->smarty->display('taken/corvee-repetitie/beheer_corvee_repetitie_lijst.tpl');
		}
	}

}

?>