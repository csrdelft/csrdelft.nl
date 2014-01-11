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
		$this->assign('roodschopper', $this->roodschopper);
		$this->assign('melding', $this->getMelding());
		$this->display('roodschopper/roodschopper.tpl');
	}

}
