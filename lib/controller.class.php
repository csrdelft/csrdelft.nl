<?php
/*
 * class.controller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Een controller zorgt ervoor dat de juiste acties uitgevoerd worden,
 * dat er gecontroleerd wordt of de gebruiker mag doen dat hij probeert
 * te doen, en dat er een content-ding aangemaakt wordt voor de huidige
 * actie.
 */

require_once 'pagina.class.php';
require_once 'paginacontent.class.php';

class Controller{

	protected $action='default';
	protected $content=null;

	private $queryparts=array();

	protected $valid=true;
	protected $errors='';

	
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
	public function addError($error){
		$this->valid=false;
		$this->errors.=$error.'<br />';
	}

	protected function action_default(){
		return true;
	}
	protected function action_geentoegang(){
		$this->content=new PaginaContent(new Pagina('geentoegang'));
	}
}
?>
