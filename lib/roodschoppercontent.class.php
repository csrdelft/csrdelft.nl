<?php

class RoodschopperContent extends TemplateView {

	private $roodschopper;

	public function __construct($roodschopper) {
		parent::__construct();
		$this->roodschopper = $roodschopper;
	}

	public function getTitel() {
		return 'Roodschopper';
	}

	public function view() {
		$this->smarty->assign('roodschopper', $this->roodschopper);
		$this->smarty->display('roodschopper/roodschopper.tpl');
	}

}
