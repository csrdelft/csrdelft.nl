<?php

namespace CsrDelft\view\maalcie\corvee\vrijstellingen;

use CsrDelft\model\entity\maalcie\CorveeVrijstelling;
use CsrDelft\view\SmartyTemplateView;

class BeheerVrijstellingView extends SmartyTemplateView {

	public function __construct(CorveeVrijstelling $vrijstelling) {
		parent::__construct($vrijstelling);
	}

	public function view() {
		$this->smarty->assign('vrijstelling', $this->model);
		$this->smarty->display('maalcie/vrijstelling/beheer_vrijstelling_lijst.tpl');
	}

}
