<?php

/**
 * Controller.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een controller zorgt ervoor dat de juiste acties uitgevoerd worden,
 * dat er gecontroleerd wordt of de gebruiker mag doen dat hij probeert
 * te doen, en dat er een content-ding aangemaakt wordt voor de huidige
 * actie.
 * 
 */
abstract class Controller {

	private $kvp = false;
	private $queryparts = array();
	protected $action = '';
	protected $content = null;

	public function __construct($querystring) {
		$this->kvp = strpos($querystring, '?');
		if ($this->kvp === false) { // REST
			$this->queryparts = explode('/', $querystring);
		} else { // KVP
			$querystring = substr($querystring, $kvp);
			$queryparts = explode('&', $querystring);
			foreach ($queryparts as $i => $part) {
				$this->queryparts[$i] = explode('=', $part);
			}
		}
	}

	protected function hasParam($key) {
		if (!array_key_exists($key, $this->queryparts) || !isset($this->queryparts[$key])) {
			return false;
		}
		if ($this->kvp === false) {
			return $this->queryparts[$key] !== '';
		}
		return true;
	}

	protected function getParam($key) {
		if ($this->hasParam($key)) {
			return $this->queryparts[$key];
		}
	}

	protected function isPOSTed() {
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

	public function getContent() {
		return $this->content;
	}

	public function hasAction($action) {
		return method_exists($this, $action);
	}

	abstract protected function hasPermission();

	protected function performAction(array $args = array()) {
		if (!$this->hasPermission()) {
			$this->action = 'geentoegang';
		}
		if (!$this->hasAction($this->action)) {
			throw new Exception('Action undefined: ' . $this->action);
		}
		call_user_func_array(array($this, $this->action), $args);
	}

	protected function geentoegang() {
		header('HTTP/1.0 403 Forbidden');
	}

}

?>