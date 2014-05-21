<?php

/**
 * CorveeRoosterView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van het corveerooster.
 * 
 */
class CorveeRoosterView extends TemplateView {

	public function __construct($rooster, $toonverleden = false) {
		parent::__construct($rooster, 'Corveerooster');
		$this->smarty->assign('rooster', $this->model);
		$this->smarty->assign('toonverleden', $toonverleden);
	}

	public function view() {
		$this->smarty->display('taken/menu_pagina.tpl');
		$this->smarty->display('taken/corveetaak/corvee_rooster.tpl');
	}

}
