<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\view\SmartyTemplateView;

/**
 * MaalCieSaldiView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van een upload tool voor het bijwerken de MaalCie saldi op de stek.
 *
 */
class MaalCieBoekjaarSluitenView extends SmartyTemplateView {

	public function __construct() {
		parent::__construct(null, 'Boekjaar sluiten');
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/boekjaar_sluiten.tpl');
	}

}
