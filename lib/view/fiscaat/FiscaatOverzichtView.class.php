<?php

class FiscaatOverzichtView extends SmartyTemplateView {
	public function view() {
		$this->smarty->display('fiscaat/overzicht.tpl');
	}
}