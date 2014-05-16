<?php

require_once 'MVC/model/DebugLogModel.class.php';

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
	 * Data access model
	 * @var Model
	 */
	protected $model;
	/**
	 * The view to be shown
	 * @var View
	 */
	protected $view;
	/**
	 * Action to be performed
	 * @var string
	 */
	protected $action;
	/**
	 * Broken down query to (named) parameters
	 * @var array
	 */
	private $queryparts;
	/**
	 * Is this controller called with a server request query containing
	 * key-value-pair (KVP) or only representational state transfer (REST)
	 * @var boolean
	 */
	private $kvp;

	public function __construct($query, PersistenceModel $model = null) {
		$this->model = $model;
		// split at ?-mark
		$mark = strpos($query, '?');
		if ($mark !== false) {
			$rest = substr($query, 0, $mark);
		} else {
			$rest = $query;
		}
		// parse REST
		$rest = explode('/', $rest);
		$this->queryparts = $rest; // add REST params
		// parse KVP
		if ($mark) {
			$mark = explode('&', substr($query, $mark));
			foreach ($mark as $key => $value) {
				$this->queryparts[$key] = explode('=', $value); // add KVP params
			}
			$this->kvp = true;
		}
	}

	/**
	 * KVP: named parameters
	 * REST: positional parameters
	 * 
	 * @param string $key
	 * @return boolean
	 */
	protected function hasParam($key) {
		return array_key_exists($key, $this->queryparts) AND isset($this->queryparts[$key]) AND $this->queryparts[$key] !== '';
	}

	/**
	 * KVP: named parameters
	 * REST: positional parameters
	 * 
	 * @param string $key
	 * @return boolean
	 */
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

	/**
	 * If request method is POST.
	 * 
	 * @return boolean
	 */
	public function isPosted() {
		return isPosted();
	}

	public function getContent() {
		return $this->view;
	}

	/**
	 * If named action is defined.
	 * 
	 * @param string $action
	 * @return boolean
	 */
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
		//DebugLogModel::instance()->log(get_called_class(), $this->action, $args);
		call_user_func_array(array($this, $this->action), $args);
	}

	protected function geentoegang() {
		http_response_code(403);
		DebugLogModel::instance()->log(get_called_class(), $this->action, array(), 'geentoegang');
		if ($this->isPosted()) {
			echo 'access denied';
		} else {
			require_once 'MVC/model/CmsPaginaModel.class.php';
			require_once 'MVC/view/CmsPaginaView.class.php';
			$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
			$this->view = new CsrLayoutPage($body);
			$this->view->view();
		}
		exit;
	}

}
