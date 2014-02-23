<?php

/**
 * Controller.abstract.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een controller zorgt ervoor dat de juiste acties uitgevoerd worden,
 * dat er gecontroleerd wordt of de gebruiker mag doen dat hij probeert
 * te doen, en dat er een View aangemaakt wordt.
 * 
 */
abstract class Controller {

	/**
	 * Is this controller called with key-value-pair (KVP)
	 * or representational state transfer (REST)
	 * @var boolean
	 */
	private $kvp = false;
	/**
	 * Broken down query to (named) parameters
	 * @var array
	 */
	private $queryparts = array();
	/**
	 * Action to be performed
	 * @var string
	 */
	protected $action = '';
	/**
	 * The view to be shown
	 * @var View
	 */
	protected $view = null;

	public function __construct($querystring) {
		$this->kvp = strpos($querystring, '?');
		if ($this->isRest()) { // REST
			$this->queryparts = explode('/', $querystring);
		} else { // KVP
			$this->kvp = true;
			$querystring = substr($querystring, $this->kvp);
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
		if ($this->isRest()) {
			return $this->queryparts[$key] !== '';
		}
		return true;
	}

	protected function getParam($key) {
		if ($this->hasParam($key)) {
			return $this->queryparts[$key];
		}
	}

	/**
	 * Return query paramter values.
	 * 
	 * @param int $skip do not return number of paramters
	 * @return array
	 */
	protected function getParams($skip = 0) {
		$params = array_values($this->queryparts);
		for ($i = 0; $i < $skip; $i++) {
			unset($params[$i]);
		}
		return $params;
	}

	public function isPosted() {
		return isPosted();
	}

	public function isKvp() {
		return $this->kvp;
	}

	public function isRest() {
		return !$this->kvp;
	}

	public function getContent() {
		return $this->view;
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
		if ($this->isPosted()) {
			echo 'access denied';
		} else {
			require_once 'MVC/model/CmsPaginaModel.class.php';
			require_once 'MVC/view/CmsPaginaView.class.php';

			$model = new CmsPaginaModel();
			$pagina = $model->getPagina('geentoegang');
			$body = new CmsPaginaView($pagina);
			$this->view = new csrdelft($body);
			$this->view->view();
		}
		exit;
	}

}
