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
	public function getTitel(){
		return ''.$_GET['gtype'].' - '.$this->groep->getNaam();
	}
	public function view(){
		$content=new Smarty_csr();
		
		$content->assign('groep', $this->groep);
		
		$content->assign('action', $this->action);
		$content->assign('gtype', $_GET['gtype']);
		$content->assign('groeptypes', Groepen::getGroeptypes());
		
				
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
	public function getTitel(){
		return 'Groepen - '.$this->groepen->getNaam();
	}
	
	public function view(){
		$content=new Smarty_csr();
		
		$content->assign('groepen', $this->groepen);
		
		$content->assign('action', $this->action);
		$content->assign('gtype', $this->groepen->getNaam());
		$content->assign('groeptypes', Groepen::getGroeptypes());
		
		$content->assign('melding', $this->getMelding());
		$content->display('groepen/groepen.tpl');		
		
	}
}
?>
