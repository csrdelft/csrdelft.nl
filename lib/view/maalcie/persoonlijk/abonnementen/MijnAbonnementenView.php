<?php

namespace CsrDelft\view\maalcie\persoonlijk\abonnementen;

use CsrDelft\view\SmartyTemplateView;

/**
 * MijnAbonnementenView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van abonnementen die een lid aan of uit kan zetten.
 *
 */
class MijnAbonnementenView extends SmartyTemplateView {

	public function __construct($abonnementen) {
		parent::__construct($abonnementen, 'Mijn abonnementen');
	}

	public function view() {
		$this->smarty->assign('abonnementen', $this->model);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/abonnement/mijn_abonnementen.tpl');
	}

}
