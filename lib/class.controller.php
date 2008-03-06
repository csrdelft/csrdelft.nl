<?php
/*
 * class.controller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */
class Controller{
	
	protected $action='default';
	protected $content=null;
	
	public function __construct(){
		
	}
	public function getContent(){
		return $this->content;
	}
	public function hasAction($action){
		return method_exists($this, 'action_'.$action);
	}
	
	protected function isPOSTed(){
		return $_SERVER['REQUEST_METHOD']=='POST';
	}
	//call the action
	protected function performAction(){
		$action='action_'.$this->action;
		if($this->hasAction($this->action)){
			$this->$action();
		}else{
			throw new Exception('Action undefined');
		}
	}
	protected function action_default(){
		return true;
	}
}
?>
