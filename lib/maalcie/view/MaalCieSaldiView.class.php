<?php

/**
 * MaalCieSaldiView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van een upload tool voor het bijwerken de MaalCie saldi op de stek.
 * 
 */
class MaalCieSaldiView extends TemplateView {

	public function __construct() {
		parent::__construct(null, 'MaalCie-saldi uploaden met een CSV-bestand');
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/maalcie_saldi.tpl');
	}

}
