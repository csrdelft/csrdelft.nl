<?php

/**
 * CorveeRepetitiesView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle corvee-repetities om te beheren.
 * 
 */
class CorveeRepetitiesView extends SmartyTemplateView {

	public function __construct($repetities, $maaltijdrepetitie = null) {
		parent::__construct($repetities);

		if ($maaltijdrepetitie !== null) {
			$this->smarty->assign('maaltijdrepetitie', $maaltijdrepetitie);

			$this->titel = 'Corveebeheer maaltijdrepetitie: ' . $maaltijdrepetitie->getStandaardTitel();
		} else {
			$this->titel = 'Beheer corveerepetities';
		}
		$this->smarty->assign('repetities', $this->model);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/corvee-repetitie/beheer_corvee_repetities.tpl');
	}

}

class CorveeRepetitieView extends SmartyTemplateView {

	public function __construct(CorveeRepetitie $repetitie) {
		parent::__construct($repetitie);
		$this->smarty->assign('repetitie', $this->model);
	}

	public function view() {
		echo '<tr id="maalcie-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
		$this->smarty->display('maalcie/corvee-repetitie/beheer_corvee_repetitie_lijst.tpl');
	}

}
