<?php

namespace CsrDelft\view\mededelingen;

use CsrDelft\view\SmartyTemplateView;

class MededelingenOverzichtView extends SmartyTemplateView {

	public function __construct() {
		parent::__construct(null, 'Top 3 mededelingenoverzicht');
	}

	public function view() {
		$this->smarty->display('mededelingen/top3overzicht.tpl');
	}

}
