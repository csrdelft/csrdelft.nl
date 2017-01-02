<?php

/**
 * MaaltijdRepetitiesView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle maaltijd-repetities om te beheren.
 * 
 */
class MaaltijdRepetitiesView extends SmartyTemplateView {

	public function __construct($repetities) {
		parent::__construct($repetities, 'Beheer maaltijdrepetities');
	}

	public function view() {
		$this->smarty->assign('repetities', $this->model);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/maaltijd-repetitie/beheer_maaltijd_repetities.tpl');
	}

}

class MaaltijdRepetitieView extends SmartyTemplateView {

	public function __construct(MaaltijdRepetitie $repetitie) {
		parent::__construct($repetitie);
	}

	public function view() {
		$this->smarty->assign('repetitie', $this->model);
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		$this->smarty->display('maalcie/maaltijd-repetitie/beheer_maaltijd_repetitie_lijst.tpl');
	}

}
