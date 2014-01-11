<?php


class RoodschopperContent extends SimpleHTML{
	private $roodschopper;
	
	public function __construct($roodschopper){
		$this->roodschopper=$roodschopper;
	}
	public function getTitel(){
		return 'Roodschopper';
	}
	public function view(){
		$content=new TemplateEngine();

		
		$content->assign('roodschopper', $this->roodschopper);
		$content->assign('melding', $this->getMelding());
		$content->display('roodschopper/roodschopper.tpl');
	}
}
