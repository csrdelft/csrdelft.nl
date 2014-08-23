<?php

class RoodschopperContent extends SmartyTemplateView {

	public function __construct(Roodschopper $roodschopper) {
		parent::__construct($roodschopper, 'Roodschopper');
	}

	public function view() {
		$this->smarty->assign('roodschopper', $this->model);
		$this->smarty->display('roodschopper/roodschopper.tpl');
	}

}
