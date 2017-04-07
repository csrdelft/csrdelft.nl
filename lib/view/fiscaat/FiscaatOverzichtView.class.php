<?php

class FiscaatOverzichtView extends SmartyTemplateView {
	public function view() {
		$this->smarty->display('fiscaat/overzicht.tpl');
	}

	public function getBreadcrumbs() {
		return '<a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » Overzicht';
	}
}