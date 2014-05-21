<?php

/**
 * BeheerVrijstellingenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle vrijstellingen om te beheren.
 * 
 */
class BeheerVrijstellingenView extends TemplateView {

	public function __construct(array $vrijstellingen) {
		parent::__construct($vrijstellingen, 'Beheer vrijstellingen');
		$this->smarty->assign('vrijstellingen', $this->model);
	}

	public function view() {
		$this->smarty->display('taken/menu_pagina.tpl');
		$this->smarty->display('taken/vrijstelling/beheer_vrijstellingen.tpl');
	}

}

class BeheerVrijstellingView extends TemplateView {

	public function __construct(CorveeVrijstelling $vrijstelling) {
		parent::__construct($vrijstelling);
		$this->smarty->assign('vrijstelling', $this->model);
	}

	public function view() {
		$this->smarty->display('taken/vrijstelling/beheer_vrijstelling_lijst.tpl');
	}

}
