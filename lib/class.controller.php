<?php
/*
 * class.controller.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Een controller zorgt ervoor dat de juiste acties uitgevoerd worden,
 * dat er gecontroleerd wordt of de gebruiker mag doen dat hij probeert
 * te doen, en dat er een content-ding aangemaakt wordt voor de huidige
 * actie.
 */

require_once 'class.pagina.php';
require_once 'class.paginacontent.php';

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
	protected function action_geentoegang(){
		$this->content=new PaginaContent(new Pagina('geentoegang'));
	}
}
?>
