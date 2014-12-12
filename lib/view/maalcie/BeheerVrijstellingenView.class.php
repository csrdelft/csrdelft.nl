<?php

/**
 * BeheerVrijstellingenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle vrijstellingen om te beheren.
 * 
 */
class BeheerVrijstellingenView extends SmartyTemplateView {

	public function __construct(array $vrijstellingen) {
		parent::__construct($vrijstellingen, 'Beheer vrijstellingen');
	}

	public function view() {
		$this->smarty->assign('vrijstellingen', $this->model);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/vrijstelling/beheer_vrijstellingen.tpl');
	}

}

class BeheerVrijstellingView extends SmartyTemplateView {

	public function __construct(CorveeVrijstelling $vrijstelling) {
		parent::__construct($vrijstelling);
	}

	public function view() {
		$this->smarty->assign('vrijstelling', $this->model);
		$this->smarty->display('maalcie/vrijstelling/beheer_vrijstelling_lijst.tpl');
	}

}
