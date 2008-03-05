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
		$content->assign('gtype', $this->groep->getType());
		
		$content->assign('melding', $this->getMelding());
		$content->display('groepen/groep.tpl');		
		
	}
}
class Groepencontent extends SimpleHTML{
	
	private $groepen;
	private $action='view';
	
	public function __construct($groepen){
		$this->groepen=$groepen;
	}
	public function setAction($action){
		$this->action=$action;
	}
	
	public function view(){
		$content=new Smarty_csr();
		
		$content->assign('groepen', $this->groepen);
		
		$content->assign('action', $this->action);
		$content->assign('gtype', $this->groepen->getNaam());
		
		$content->assign('melding', $this->getMelding());
		$content->display('groepen/groepen.tpl');		
		
	}
}
?>
