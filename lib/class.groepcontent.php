<?php
/*
 * class.groepcontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */


class Groepcontent extends SimpleHTML{
	
	private $groep;
	private $action='view';
	
	public function __construct($groep){
		$this->groep=$groep;
	}
	public function setAction($action){
		$this->action=$action;
	}
	
	public function view(){
		$content=new Smarty_csr();
		
		$content->assign('groep', $this->groep);
		
		$content->assign('action', $this->action);
		
		$content->assign('melding', $this->getMelding());
		$content->display('groepen/groep.tpl');		
		
	}
}
?>
