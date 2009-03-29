<?php
/*
 * class.controller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
class Controller{

	protected $action='default';
	protected $content=null;

	private $queryparts=array();

	public function __construct($querystring){
		$this->queryparts=explode('/', $querystring);

	}
	public function hasParam($key){
		return isset($this->queryparts[$key]);
	}
	public function getParam($key){
		if($this->hasParam($key)){
			return $this->queryparts[$key];
		}
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
			throw new Exception('Action ('.$this->action.') undefined');
		}
	}
	protected function action_default(){
		return true;
	}
}
?>
