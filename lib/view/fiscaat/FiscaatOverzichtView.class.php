<?php

require_once 'view/fiscaat/BeheerCiviSaldoView.class.php';
require_once 'view/fiscaat/BeheerCiviProductenView.class.php';

class FiscaatOverzichtView extends SmartyTemplateView {
	public function view() {
		$this->smarty->assign('productenbeheer', new BeheerCiviProductenView());
		$this->smarty->assign('saldobeheer', new BeheerCiviSaldoView());
		$this->smarty->display('fiscaat/overzicht.tpl');
	}

	public function getBreadcrumbs() {
		return '<a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » Overzicht';
	}
}