<?php

/**
 * CorveeRepetitiesView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle corvee-repetities om te beheren.
 * 
 */
class CorveeRepetitiesView extends TemplateView {

	private $maaltijdrepetitie;

	public function __construct($repetities, $maaltijdrepetitie = null) {
		parent::__construct($repetities);
		$this->maaltijdrepetitie = $maaltijdrepetitie;
	}

	public function getTitel() {
		if ($this->maaltijdrepetitie !== null) {
			return 'Corveebeheer maaltijdrepetitie: ' . $this->maaltijdrepetitie->getStandaardTitel();
		}
		return 'Beheer corveerepetities';
	}

	public function view() {
		if ($this->maaltijdrepetitie !== null) {
			$this->smarty->assign('maaltijdrepetitie', $this->maaltijdrepetitie);
		}
		if (is_array($this->model)) { // list of repetities
			$this->smarty->display('taken/menu_pagina.tpl');
			$this->smarty->assign('repetities', $this->model);
			$this->smarty->display('taken/corvee-repetitie/beheer_corvee_repetities.tpl');
		} elseif (is_int($this->model)) { // id of deleted repetitie
			echo '<tr id="taken-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
			echo '<tr id="repetitie-row-' . $this->model . '" class="remove"></tr>';
		} else { // single repetitie
			echo '<tr id="taken-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
			$this->smarty->assign('repetitie', $this->model);
			$this->smarty->display('taken/corvee-repetitie/beheer_corvee_repetitie_lijst.tpl');
		}
	}

}
