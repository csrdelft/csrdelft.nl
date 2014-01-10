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

	private $queryparts;
	private $action;
	protected $content;

	public function __construct($querystring) {
		$kvp = strpos($querystring, '?');
		if ($kvp !== FALSE) { // KVP
			$querystring = substr($querystring, $kvp);
			$this->queryparts = explode('&', $querystring);
			foreach ($this->queryparts as $i => $part) {
				$this->queryparts[$i] = explode('=', $part);
			}
		} else { // REST
			$this->queryparts = explode('/', $querystring);
		}
	}

	protected function hasParam($key) {
		return isset($this->queryparts[$key]) && isset($this->queryparts[$key]);
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
		if (parent::isPOSTed()) {
			return $this->content;
		}
		return new csrdelft($this->content);
	}

	public function hasAction($action) {
		return method_exists($this, $action);
	}

	abstract protected function hasPermission($action);

	protected function performAction(array $args) {
		$action = 'action_' . $this->action;
		if ($this->hasAction($this->action)) {
			if (!$this->hasPermission($this->action)) {
				$this->action = 'geentoegang';
			}
			call_user_func_array(array($this, $action), $args);
		} else {
			throw new Exception('Action undefined: ' . $this->action);
		}
	}

	protected function geentoegang() {
		header('HTTP/1.0 403 Forbidden');
	}

}

?>