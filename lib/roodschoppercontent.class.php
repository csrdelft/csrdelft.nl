<?php

class RoodschopperContent extends TemplateView {

	public function __construct(Roodschopper $roodschopper) {
		parent::__construct($roodschopper, 'Roodschopper');
		$this->smarty->assign('roodschopper', $this->model);
	}

	public function view() {
		$this->smarty->display('roodschopper/roodschopper.tpl');
	}

}
